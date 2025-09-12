<?php

namespace App\Filament\Cms\Resources\ApplicationFormResource\Pages;

use App\Filament\Cms\Resources\ApplicationFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApplicationForm extends EditRecord
{
    protected static string $resource = ApplicationFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
