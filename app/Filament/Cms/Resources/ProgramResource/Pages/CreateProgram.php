<?php

namespace App\Filament\Cms\Resources\ProgramResource\Pages;

use App\Filament\Cms\Resources\ProgramResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProgram extends CreateRecord
{
    protected static string $resource = ProgramResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organization_id'] = Auth::user()->organization_id;

        return $data;
    }
}
