<?php

namespace App\Filament\Resources\IndividualResource\Pages;

use App\Filament\Resources\IndividualResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIndividual extends EditRecord
{
    protected static string $resource = IndividualResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Individual profile updated successfully';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reset_password')
                ->label('Reset Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->action(function () {
                    // TODO: Implement password reset functionality
                })
                ->requiresConfirmation()
                ->modalHeading('Reset User Password')
                ->modalDescription('This will send a password reset email to the user.')
                ->visible(fn (): bool => $this->record->user !== null),

            Actions\DeleteAction::make()
                ->modalHeading('Delete Individual Profile')
                ->modalDescription('Are you sure you want to delete this individual profile? This action cannot be undone, but the user account will remain intact.'),
        ];
    }
}
