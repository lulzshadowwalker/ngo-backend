<?php

namespace App\Filament\Cms\Resources\OpportunityResource\Pages;

use App\Filament\Cms\Resources\OpportunityResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOpportunity extends CreateRecord
{
    protected static string $resource = OpportunityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organization_id'] = Auth::user()->organization_id;

        return $data;
    }
}
