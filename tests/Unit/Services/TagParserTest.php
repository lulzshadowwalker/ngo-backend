<?php

namespace Tests\Unit\Services;

use App\Services\TagParser;
use InvalidArgumentException;
use Tests\TestCase;

class TagParserTest extends TestCase
{
    private TagParser $tagParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagParser = new TagParser();
    }

    /** @test */
    public function it_parses_simple_comma_separated_tags()
    {
        $input = "tag1,tag2,tag3";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_handles_null_input()
    {
        $result = $this->tagParser->parse(null);
        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_handles_empty_string_input()
    {
        $result = $this->tagParser->parse("");
        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_handles_whitespace_only_input()
    {
        $result = $this->tagParser->parse("   ");
        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_trims_whitespace_from_tags()
    {
        $input = " tag1 , tag2  ,  tag3 ";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_removes_empty_tags()
    {
        $input = "tag1,,tag2,   ,tag3";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_removes_duplicate_tags_by_default()
    {
        $input = "tag1,tag2,tag1,tag3,tag2";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_allows_duplicates_when_specified()
    {
        $input = "tag1,tag2,tag1,tag3";
        $result = $this->tagParser->parse($input, ",", false, true);

        $this->assertEquals(["tag1", "tag2", "tag1", "tag3"], $result);
    }

    /** @test */
    public function it_handles_case_insensitive_duplicates_by_default()
    {
        $input = "Tag1,TAG1,tag1,Tag2";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag1", "tag2"], $result);
    }

    /** @test */
    public function it_handles_case_sensitive_parsing()
    {
        $input = "Tag1,TAG1,tag1,Tag2";
        $result = $this->tagParser->parse($input, ",", true, false);

        $this->assertEquals(["Tag1", "TAG1", "tag1", "Tag2"], $result);
    }

    /** @test */
    public function it_uses_custom_delimiter()
    {
        $input = "tag1|tag2|tag3";
        $result = $this->tagParser->parse($input, "|");

        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_handles_semicolon_delimiter()
    {
        $input = "tag1;tag2;tag3";
        $result = $this->tagParser->parse($input, ";");

        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_throws_exception_for_empty_delimiter()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Delimiter cannot be empty or whitespace only."
        );

        $this->tagParser->parse("tag1,tag2", "");
    }

    /** @test */
    public function it_throws_exception_for_whitespace_only_delimiter()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Delimiter cannot be empty or whitespace only."
        );

        $this->tagParser->parse("tag1,tag2", "   ");
    }

    /** @test */
    public function it_respects_max_tags_limit()
    {
        $input = "tag1,tag2,tag3,tag4,tag5";
        $result = $this->tagParser->parse($input, ",", false, false, 3);

        $this->assertCount(3, $result);
        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_throws_exception_for_negative_max_tags()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Maximum tags count cannot be negative.");

        $this->tagParser->parse("tag1,tag2", ",", false, false, -1);
    }

    /** @test */
    public function it_accepts_zero_max_tags()
    {
        $input = "tag1,tag2,tag3";
        $result = $this->tagParser->parse($input, ",", false, false, 0);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_cleans_special_characters()
    {
        $input = "tag@1,tag#2,tag$3";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_preserves_hyphens_and_underscores()
    {
        $input = "tag-1,tag_2,multi-word_tag";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag-1", "tag_2", "multi-word_tag"], $result);
    }

    /** @test */
    public function it_handles_unicode_characters()
    {
        $input = "тег,العربية,中文";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["тег", "العربية", "中文"], $result);
    }

    /** @test */
    public function it_normalizes_multiple_spaces()
    {
        $input = "tag   with   spaces,another    tag";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag with spaces", "another tag"], $result);
    }

    /** @test */
    public function it_skips_overly_long_tags()
    {
        $longTag = str_repeat("a", 101); // Exceeds MAX_TAG_LENGTH
        $input = "tag1,{$longTag},tag2";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag1", "tag2"], $result);
    }

    /** @test */
    public function it_handles_max_tag_length_boundary()
    {
        $maxLengthTag = str_repeat("a", 100); // Exactly MAX_TAG_LENGTH
        $input = "tag1,{$maxLengthTag},tag2";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag1", $maxLengthTag, "tag2"], $result);
    }

    /** @test */
    public function it_enforces_global_max_tags_limit()
    {
        $tags = array_map(fn($i) => "tag{$i}", range(1, 60));
        $input = implode(",", $tags);
        $result = $this->tagParser->parse($input);

        $this->assertCount(50, $result); // MAX_TAGS_COUNT
    }

    /** @test */
    public function it_parses_to_string()
    {
        $input = "tag1, tag2 , tag3";
        $result = $this->tagParser->parseToString($input);

        $this->assertEquals("tag1, tag2, tag3", $result);
    }

    /** @test */
    public function it_parses_to_empty_string_for_null_input()
    {
        $result = $this->tagParser->parseToString(null);
        $this->assertEquals("", $result);
    }

    /** @test */
    public function it_provides_simple_parse_method()
    {
        $input = "tag1, tag2, tag3";
        $result = $this->tagParser->parseSimple($input);

        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_provides_strict_parse_method()
    {
        $input = "Tag1,tag1,TAG1,Tag2";
        $result = $this->tagParser->parseStrict($input);

        $this->assertEquals(["Tag1", "tag1", "TAG1", "Tag2"], $result);
    }

    /** @test */
    public function it_provides_relaxed_parse_method()
    {
        $input = "tag1,tag1,tag2";
        $result = $this->tagParser->parseRelaxed($input);

        $this->assertEquals(["tag1", "tag1", "tag2"], $result);
    }

    /** @test */
    public function it_validates_input()
    {
        $this->assertTrue($this->tagParser->isValid("tag1,tag2"));
        $this->assertFalse($this->tagParser->isValid(""));
        $this->assertFalse($this->tagParser->isValid(null));
        $this->assertFalse($this->tagParser->isValid("   "));
    }

    /** @test */
    public function it_handles_validation_with_custom_delimiter()
    {
        $this->assertTrue($this->tagParser->isValid("tag1|tag2", "|"));
        $this->assertTrue($this->tagParser->isValid("tag1,tag2", ","));
    }

    /** @test */
    public function it_counts_tags()
    {
        $this->assertEquals(3, $this->tagParser->count("tag1,tag2,tag3"));
        $this->assertEquals(0, $this->tagParser->count(""));
        $this->assertEquals(0, $this->tagParser->count(null));
        $this->assertEquals(2, $this->tagParser->count("tag1,tag2,tag1")); // Duplicates removed
    }

    /** @test */
    public function it_provides_max_tag_length()
    {
        $this->assertEquals(100, $this->tagParser->getMaxTagLength());
    }

    /** @test */
    public function it_provides_max_tags_count()
    {
        $this->assertEquals(50, $this->tagParser->getMaxTagsCount());
    }

    /** @test */
    public function it_handles_complex_real_world_scenarios()
    {
        $input =
            " Laravel , PHP,  Web Development, Backend,   API , , REST,   GraphQL  ";
        $result = $this->tagParser->parse($input);

        $expected = [
            "laravel",
            "php",
            "web development",
            "backend",
            "api",
            "rest",
            "graphql",
        ];
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_handles_mixed_case_duplicates_in_real_world_scenario()
    {
        $input = "JavaScript,javascript,JAVASCRIPT,React,react,Vue.js";
        $result = $this->tagParser->parse($input);

        $expected = ["javascript", "react", "vuejs"];
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_handles_edge_case_with_only_delimiters()
    {
        $input = ",,,";
        $result = $this->tagParser->parse($input);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_handles_edge_case_with_mixed_whitespace_and_delimiters()
    {
        $input = " , , , ";
        $result = $this->tagParser->parse($input);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_maintains_order_of_unique_tags()
    {
        $input = "third,first,second,first";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["third", "first", "second"], $result);
    }

    /** @test */
    public function it_handles_newlines_and_tabs()
    {
        $input = "tag1\n,\ttag2,tag3\r\n";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["tag1", "tag2", "tag3"], $result);
    }

    /** @test */
    public function it_handles_numeric_tags()
    {
        $input = "2023,2024,version-1.0,build-123";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(
            ["2023", "2024", "version-10", "build-123"],
            $result
        );
    }

    /** @test */
    public function it_handles_single_tag()
    {
        $input = "single-tag";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["single-tag"], $result);
    }

    /** @test */
    public function it_handles_single_tag_with_whitespace()
    {
        $input = "  single-tag  ";
        $result = $this->tagParser->parse($input);

        $this->assertEquals(["single-tag"], $result);
    }
}
