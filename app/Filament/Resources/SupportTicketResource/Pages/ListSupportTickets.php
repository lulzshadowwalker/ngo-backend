<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Enums\SupportTicketStatus;
use App\Filament\Resources\SupportTicketResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupportTickets extends ListRecords
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make(),
        ];

        if (app()->environment('local')) {
            $actions[] = Actions\Action::make('create_mock_tickets')
                ->label('Create Mock Tickets')
                ->icon('heroicon-o-beaker')
                ->color('gray')
                ->action(function () {
                    $users = User::all();

                    if ($users->isEmpty()) {
                        return;
                    }

                    $statuses = [
                        SupportTicketStatus::Open,
                        SupportTicketStatus::InProgress,
                        SupportTicketStatus::Resolved,
                    ];

                    for ($i = 0; $i < 10; $i++) {
                        \App\Models\SupportTicket::create([
                            'number' => 'TICKET-'.str_pad(
                                (\App\Models\SupportTicket::max('id') ?? 0) + $i + 1,
                                6,
                                '0',
                                STR_PAD_LEFT
                            ),
                            'subject' => fake()->sentence(),
                            'message' => fake()->paragraphs(3, true),
                            'status' => fake()->randomElement($statuses),
                            'user_id' => $users->random()->id,
                        ]);
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Create Mock Support Tickets')
                ->modalDescription('This will create 10 mock support tickets with random data. This action is only available in local environment.');
        }

        return $actions;
    }
}
