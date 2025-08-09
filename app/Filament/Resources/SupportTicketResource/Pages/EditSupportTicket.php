<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Enums\SupportTicketStatus;
use App\Filament\Resources\SupportTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupportTicket extends EditRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Support ticket updated successfully';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_in_progress')
                ->label('Mark In Progress')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => $this->record->status === SupportTicketStatus::Open)
                ->action(function () {
                    $this->record->markAsInProgress();
                    $this->refreshFormData(['status']);
                })
                ->requiresConfirmation()
                ->modalHeading('Mark ticket as In Progress')
                ->modalDescription('Are you sure you want to mark this ticket as in progress?'),

            Actions\Action::make('mark_resolved')
                ->label('Mark Resolved')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->status !== SupportTicketStatus::Resolved)
                ->action(function () {
                    $this->record->markAsResolved();
                    $this->refreshFormData(['status']);
                })
                ->requiresConfirmation()
                ->modalHeading('Mark ticket as Resolved')
                ->modalDescription('Are you sure you want to mark this ticket as resolved?'),

            Actions\Action::make('reopen')
                ->label('Reopen Ticket')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('info')
                ->visible(fn (): bool => $this->record->status === SupportTicketStatus::Resolved)
                ->action(function () {
                    $this->record->markAsOpen();
                    $this->refreshFormData(['status']);
                })
                ->requiresConfirmation()
                ->modalHeading('Reopen ticket')
                ->modalDescription('Are you sure you want to reopen this ticket?'),

            Actions\DeleteAction::make(),
        ];
    }
}
