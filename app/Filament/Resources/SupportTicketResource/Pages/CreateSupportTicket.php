<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Enums\SupportTicketStatus;
use App\Filament\Resources\SupportTicketResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSupportTicket extends CreateRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Support ticket created successfully';
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
                    $this->form->fill([
                        'subject' => fake()->sentence(),
                        'message' => fake()->paragraphs(3, true),
                        'user_id' => User::inRandomOrder()->first()?->id,
                        'status' => SupportTicketStatus::Open,
                    ]);
                });
        }

        return $actions;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate ticket number if not provided
        if (empty($data['number'])) {
            $data['number'] = 'TICKET-'.str_pad(
                (\App\Models\SupportTicket::max('id') ?? 0) + 1,
                6,
                '0',
                STR_PAD_LEFT
            );
        }

        return $data;
    }
}
