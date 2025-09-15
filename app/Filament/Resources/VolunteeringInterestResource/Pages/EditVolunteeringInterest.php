<?php

namespace App\Filament\Resources\VolunteeringInterestResource\Pages;

use App\Filament\Resources\VolunteeringInterestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVolunteeringInterest extends EditRecord
{
    protected static string $resource = VolunteeringInterestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
