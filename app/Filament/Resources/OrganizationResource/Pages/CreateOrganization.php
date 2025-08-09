<?php

namespace App\Filament\Resources\OrganizationResource\Pages;

use App\Filament\Resources\OrganizationResource;
use App\Models\Location;
use App\Models\Sector;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrganization extends CreateRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Organization created successfully';
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
                    $orgName = fake()->company();
                    
                    $this->form->fill([
                        'name' => $orgName,
                        'slug' => Str::slug($orgName),
                        'bio' => fake()->paragraphs(3, true),
                        'website' => fake()->optional(0.8)->url(),
                        'sector_id' => Sector::inRandomOrder()->first()?->id,
                        'location_id' => Location::inRandomOrder()->first()?->id,
                    ]);
                });
        }

        return $actions;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure slug is generated if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        
        while (\App\Models\Organization::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $data;
    }
}
