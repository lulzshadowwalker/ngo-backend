<?php

namespace App\Filament\Cms\Resources\ApplicationFormResource\Pages;

use App\Filament\Cms\Resources\ApplicationFormResource;
use App\Models\ApplicationForm;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateApplicationForm extends CreateRecord
{
    protected static string $resource = ApplicationFormResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organization_id'] = Auth::user()->organization_id;

        // Check if an application form already exists for this opportunity
        if (ApplicationForm::where('opportunity_id', $data['opportunity_id'])->exists()) {
            Notification::make()
                ->title('Application Form Already Exists')
                ->body('This opportunity already has an application form. Please edit the existing form or choose a different opportunity.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Application Form Created')
            ->body('The application form has been created successfully and is ready to accept applications.');
    }
}
