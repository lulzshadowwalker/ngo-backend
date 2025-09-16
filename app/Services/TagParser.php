<?php

namespace App\Services;

use InvalidArgumentException;

class TagParser
{
    /**
     * Default delimiter for separating tags.
     */
    private const DEFAULT_DELIMITER = ',';

    /**
     * Maximum allowed tag length.
     */
    private const MAX_TAG_LENGTH = 100;

    /**
     * Maximum number of tags allowed.
     */
    private const MAX_TAGS_COUNT = 50;

    /**
     * Parse a string of comma-separated tags into an array of clean strings.
     *
     * @param  string|null  $input  The input string containing tags
     * @param  string  $delimiter  The delimiter to split tags by (default: comma)
     * @param  bool  $caseSensitive  Whether tags should be case-sensitive (default: false)
     * @param  bool  $allowDuplicates  Whether to allow duplicate tags (default: false)
     * @param  int|null  $maxTags  Maximum number of tags to return (default: null for no limit)
     * @return array<string> Array of cleaned tag strings
     *
     * @throws InvalidArgumentException When input is invalid
     */
    public function parse(
        ?string $input,
        string $delimiter = self::DEFAULT_DELIMITER,
        bool $caseSensitive = false,
        bool $allowDuplicates = false,
        ?int $maxTags = null
    ): array {
        // Handle null or empty input
        if ($input === null || trim($input) === '') {
            return [];
        }

        // Validate delimiter
        if (empty(trim($delimiter))) {
            throw new InvalidArgumentException(
                'Delimiter cannot be empty or whitespace only.'
            );
        }

        // Validate max tags
        if ($maxTags !== null && $maxTags < 0) {
            throw new InvalidArgumentException(
                'Maximum tags count cannot be negative.'
            );
        }

        // Split the input by delimiter
        $rawTags = explode($delimiter, $input);
        $cleanedTags = [];

        foreach ($rawTags as $tag) {
            $cleanedTag = $this->cleanTag($tag);

            // Skip empty tags after cleaning
            if ($cleanedTag === '') {
                continue;
            }

            // Validate tag length
            if (mb_strlen($cleanedTag) > self::MAX_TAG_LENGTH) {
                continue; // Skip overly long tags instead of throwing exception
            }

            // Handle case sensitivity
            $finalTag = $caseSensitive
                ? $cleanedTag
                : mb_strtolower($cleanedTag);

            // Handle duplicates
            if (! $allowDuplicates) {
                $compareArray = $caseSensitive
                    ? $cleanedTags
                    : array_map('mb_strtolower', $cleanedTags);
                if (in_array($finalTag, $compareArray, true)) {
                    continue;
                }
            }

            $cleanedTags[] = $caseSensitive ? $cleanedTag : $finalTag;

            // Check if we've reached the maximum number of tags
            if ($maxTags !== null && count($cleanedTags) >= $maxTags) {
                break;
            }
        }

        // Handle zero max tags case
        if ($maxTags === 0) {
            return [];
        }

        // Apply global limits
        if (count($cleanedTags) > self::MAX_TAGS_COUNT) {
            $cleanedTags = array_slice($cleanedTags, 0, self::MAX_TAGS_COUNT);
        }

        return array_values($cleanedTags);
    }

    /**
     * Parse tags and return as a comma-separated string.
     *
     * @param  string|null  $input  The input string containing tags
     * @param  string  $delimiter  The delimiter to split tags by (default: comma)
     * @param  bool  $caseSensitive  Whether tags should be case-sensitive (default: false)
     * @param  bool  $allowDuplicates  Whether to allow duplicate tags (default: false)
     * @param  int|null  $maxTags  Maximum number of tags to return (default: null for no limit)
     * @return string Comma-separated string of cleaned tags
     */
    public function parseToString(
        ?string $input,
        string $delimiter = self::DEFAULT_DELIMITER,
        bool $caseSensitive = false,
        bool $allowDuplicates = false,
        ?int $maxTags = null
    ): string {
        $tags = $this->parse(
            $input,
            $delimiter,
            $caseSensitive,
            $allowDuplicates,
            $maxTags
        );

        return implode(', ', $tags);
    }

    /**
     * Parse tags with custom settings for common use cases.
     *
     * @param  string|null  $input  The input string containing tags
     * @return array<string> Array of cleaned tag strings with default settings
     */
    public function parseSimple(?string $input): array
    {
        return $this->parse($input);
    }

    /**
     * Parse tags with strict validation (case-sensitive, no duplicates).
     *
     * @param  string|null  $input  The input string containing tags
     * @param  int|null  $maxTags  Maximum number of tags to return
     * @return array<string> Array of cleaned tag strings
     */
    public function parseStrict(?string $input, ?int $maxTags = null): array
    {
        return $this->parse(
            $input,
            self::DEFAULT_DELIMITER,
            true,
            false,
            $maxTags
        );
    }

    /**
     * Parse tags with relaxed validation (case-insensitive, allows duplicates).
     *
     * @param  string|null  $input  The input string containing tags
     * @return array<string> Array of cleaned tag strings
     */
    public function parseRelaxed(?string $input): array
    {
        return $this->parse($input, self::DEFAULT_DELIMITER, false, true);
    }

    /**
     * Clean and normalize a single tag.
     *
     * @param  string  $tag  The raw tag string
     * @return string The cleaned tag string
     */
    private function cleanTag(string $tag): string
    {
        // Trim whitespace
        $cleaned = trim($tag);

        // Remove multiple consecutive spaces and replace with single space
        $cleaned = preg_replace("/\s+/", ' ', $cleaned);

        // Remove special characters that might cause issues (but keep alphanumeric, spaces, hyphens, underscores)
        $cleaned = preg_replace("/[^\p{L}\p{N}\s\-_]/u", '', $cleaned);

        // Trim again after character removal
        $cleaned = trim($cleaned);

        return $cleaned;
    }

    /**
     * Validate if a string contains valid tags.
     *
     * @param  string|null  $input  The input string to validate
     * @param  string  $delimiter  The delimiter to split tags by
     * @return bool True if the input contains valid tags
     */
    public function isValid(
        ?string $input,
        string $delimiter = self::DEFAULT_DELIMITER
    ): bool {
        try {
            $tags = $this->parse($input, $delimiter);

            return ! empty($tags);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Count the number of valid tags in the input.
     *
     * @param  string|null  $input  The input string containing tags
     * @param  string  $delimiter  The delimiter to split tags by
     * @return int Number of valid tags
     */
    public function count(
        ?string $input,
        string $delimiter = self::DEFAULT_DELIMITER
    ): int {
        return count($this->parse($input, $delimiter));
    }

    /**
     * Get the maximum allowed tag length.
     *
     * @return int Maximum tag length
     */
    public function getMaxTagLength(): int
    {
        return self::MAX_TAG_LENGTH;
    }

    /**
     * Get the maximum allowed number of tags.
     *
     * @return int Maximum tags count
     */
    public function getMaxTagsCount(): int
    {
        return self::MAX_TAGS_COUNT;
    }
}
