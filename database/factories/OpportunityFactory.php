<?php

namespace Database\Factories;

use App\Enums\OpportunityStatus;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->jobTitle().' Opportunity',
                'ar' => 'فرصة '.$this->faker->words(2, true),
            ],
            'description' => [
                'en' => $this->faker->paragraph(),
                'ar' => $this->faker->paragraph().' باللغة العربية',
            ],
            'status' => $this->faker->randomElement(OpportunityStatus::cases()),
            'organization_id' => Organization::factory(),
            'program_id' => Program::factory(),
            'tags' => [
                'en' => implode(',', ['volunteer', 'skills', 'training']),
                'ar' => implode(',', ['تطوع', 'مهارات', 'تدريب']),
            ],
            'duration' => $this->faker->numberBetween(30, 365),
            'expiry_date' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'about_the_role' => [
                'en' => $this->faker->paragraph(),
                'ar' => $this->faker->paragraph().' باللغة العربية',
            ],
            'key_responsibilities' => [
                'en' => $this->faker->sentences(3),
                'ar' => ['مسؤولية أولى', 'مسؤولية ثانية', 'مسؤولية ثالثة'],
            ],
            'required_skills' => [
                'en' => $this->faker->sentences(3),
                'ar' => ['مهارة أولى', 'مهارة ثانية', 'مهارة ثالثة'],
            ],
            'time_commitment' => [
                'en' => ['5 hours per week', '3 months minimum', 'Flexible schedule'],
                'ar' => ['5 ساعات في الأسبوع', '3 أشهر كحد أدنى', 'جدول مرن'],
            ],
            'location_id' => Location::factory(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'location_description' => [
                'en' => $this->faker->address(),
                'ar' => 'العنوان باللغة العربية',
            ],
            'benefits' => [
                'en' => $this->faker->sentences(3),
                'ar' => ['فائدة أولى', 'فائدة ثانية', 'فائدة ثالثة'],
            ],
            'sector_id' => Sector::factory(),
            'extra' => [
                'en' => $this->faker->paragraph(),
                'ar' => $this->faker->paragraph().' معلومات إضافية',
            ],
        ];
    }
}
