<?php

namespace App\Filament\Resources\VolunteeringInterestResource\Pages;

use App\Filament\Resources\VolunteeringInterestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVolunteeringInterests extends ListRecords
{
    protected static string $resource = VolunteeringInterestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
