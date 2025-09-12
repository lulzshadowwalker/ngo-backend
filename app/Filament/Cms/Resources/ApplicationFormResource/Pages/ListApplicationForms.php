<?php

namespace App\Filament\Cms\Resources\ApplicationFormResource\Pages;

use App\Filament\Cms\Resources\ApplicationFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApplicationForms extends ListRecords
{
    protected static string $resource = ApplicationFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
