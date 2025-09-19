<?php

namespace App\Filament\Resources\IndividualResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\IndividualResource;
use App\Models\Location;
use App\Models\Skill;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIndividual extends CreateRecord
{
    protected static string $resource = IndividualResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Individual profile created successfully';
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (app()->environment('local')) {
            $actions[] = Actions\Action::make('fill_mock_data')
                ->label('Fill Mock Data')
                ->icon('heroicon-o-beaker')
                ->color('gray')
                ->action(function () {
                    // Get or create a random user with individual role
                    $user = User::individuals()->inRandomOrder()->first();

                    if (! $user) {
                        $user = User::factory()->create();
                        $user->assignRole(Role::individual->value);
                    }

                    $this->form->fill([
                        'user_id' => $user->id,
                        'bio' => fake()->paragraphs(2, true),
                        'birthdate' => fake()->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
                        'location_id' => Location::inRandomOrder()->first()?->id,
                        'skills' => Skill::inRandomOrder()->limit(rand(2, 5))->pluck('id')->toArray(),
                        'sectors' => collect([
                            ['name' => fake()->randomElement(['Environmental Protection', 'Education Support', 'Healthcare Assistance', 'Community Development', 'Youth Mentoring'])],
                            ['name' => fake()->randomElement(['Animal Welfare', 'Disaster Relief', 'Poverty Alleviation', 'Technology Training', 'Arts & Culture'])],
                        ])->random(rand(1, 2))->toArray(),
                    ]);
                });
        }

        return $actions;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure the selected user has the individual role
        if (isset($data['user_id'])) {
            $user = User::find($data['user_id']);
            if ($user && ! $user->hasRole(Role::individual->value)) {
                $user->assignRole(Role::individual->value);
            }
        }

        return $data;
    }
}
