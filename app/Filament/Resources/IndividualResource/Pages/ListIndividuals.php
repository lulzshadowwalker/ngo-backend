<?php

namespace App\Filament\Resources\IndividualResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\IndividualResource;
use App\Models\Individual;
use App\Models\Location;
use App\Models\Skill;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIndividuals extends ListRecords
{
    protected static string $resource = IndividualResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make(),
        ];

        if (app()->environment('local')) {
            $actions[] = Actions\Action::make('create_mock_individuals')
                ->label('Create Mock Individuals')
                ->icon('heroicon-o-beaker')
                ->color('gray')
                ->action(function () {
                    $locations = Location::all();
                    $skills = Skill::all();

                    for ($i = 0; $i < 15; $i++) {
                        // Create user
                        $user = User::factory()->create();
                        $user->assignRole(Role::individual->value);

                        // Create individual profile
                        $individual = Individual::create([
                            'user_id' => $user->id,
                            'bio' => fake()->paragraphs(2, true),
                            'birthdate' => fake()->dateTimeBetween('-65 years', '-18 years'),
                            'location_id' => $locations->random()?->id,
                        ]);

                        // Attach random skills
                        if ($skills->isNotEmpty()) {
                            $individual->skills()->attach(
                                $skills->random(rand(2, 5))->pluck('id')
                            );
                        }

                        // Create Sectors
                        $interests = [
                            'Environmental Protection', 'Education Support', 'Healthcare Assistance',
                            'Community Development', 'Youth Mentoring', 'Animal Welfare',
                            'Disaster Relief', 'Poverty Alleviation', 'Technology Training',
                            'Arts & Culture', 'Senior Care', 'Food Security',
                        ];

                        foreach (fake()->randomElements($interests, rand(1, 3)) as $interest) {
                            $individual->sectors()->create([
                                'name' => $interest,
                            ]);
                        }
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Create Mock Individual Profiles')
                ->modalDescription('This will create 15 mock individual profiles with users, skills, and interests. This action is only available in local environment.');
        }

        return $actions;
    }
}
