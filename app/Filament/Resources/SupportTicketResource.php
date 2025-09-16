<?php

namespace App\Filament\Resources;

use App\Enums\SupportTicketStatus;
use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Support Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $openCount = static::getModel()::where('status', SupportTicketStatus::Open)->count();

        return $openCount > 0 ? (string) $openCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $openCount = static::getModel()::where('status', SupportTicketStatus::Open)->count();

        if ($openCount === 0) {
            return 'success';
        }
        if ($openCount <= 5) {
            return 'warning';
        }

        return 'danger';
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return '#'.$record->number.' - '.$record->subject.' ('.$record->status->getLabel().')';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'User' => $record->user->name,
            'Status' => $record->status->getLabel(),
            'Created' => $record->created_at->diffForHumans(),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'subject', 'message', 'user.name', 'user.email'];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('edit', ['record' => $record]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Information')
                    ->description('Basic support ticket details and user information')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->required()
                            ->placeholder('Auto-generated ticket number')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => "{$record->name} ({$record->email})")
                            ->placeholder('Select user who submitted this ticket'),

                        Forms\Components\Select::make('status')
                            ->required()
                            ->options(SupportTicketStatus::class)
                            ->default(SupportTicketStatus::Open)
                            ->placeholder('Select ticket status'),
                    ]),

                Forms\Components\Section::make('Ticket Details')
                    ->description('Subject and detailed message content')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->placeholder('Brief description of the issue')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->placeholder('Detailed description of the issue or request...')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => str_replace('TICKET-', '', $state))
                    ->copyable()
                    ->copyMessage('Ticket number copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('User name copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($state): string => $state->format('M j, Y g:i A')),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($state): string => $state->format('M j, Y g:i A'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(SupportTicketStatus::class)
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Created from '.Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Created until '.Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('mark_in_progress')
                        ->label('Mark In Progress')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn (SupportTicket $record): bool => $record->status === SupportTicketStatus::Open)
                        ->action(fn (SupportTicket $record) => $record->markAsInProgress())
                        ->requiresConfirmation()
                        ->modalHeading('Mark ticket as In Progress')
                        ->modalDescription('Are you sure you want to mark this ticket as in progress?'),

                    Tables\Actions\Action::make('mark_resolved')
                        ->label('Mark Resolved')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (SupportTicket $record): bool => $record->status !== SupportTicketStatus::Resolved)
                        ->action(fn (SupportTicket $record) => $record->markAsResolved())
                        ->requiresConfirmation()
                        ->modalHeading('Mark ticket as Resolved')
                        ->modalDescription('Are you sure you want to mark this ticket as resolved?'),

                    Tables\Actions\Action::make('reopen')
                        ->label('Reopen')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('info')
                        ->visible(fn (SupportTicket $record): bool => $record->status === SupportTicketStatus::Resolved)
                        ->action(fn (SupportTicket $record) => $record->markAsOpen())
                        ->requiresConfirmation()
                        ->modalHeading('Reopen ticket')
                        ->modalDescription('Are you sure you want to reopen this ticket?'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('mark_in_progress')
                        ->label('Mark as In Progress')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->markAsInProgress();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Mark tickets as In Progress')
                        ->modalDescription('Are you sure you want to mark the selected tickets as in progress?')
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('mark_resolved')
                        ->label('Mark as Resolved')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->markAsResolved();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Mark tickets as Resolved')
                        ->modalDescription('Are you sure you want to mark the selected tickets as resolved?')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'create' => Pages\CreateSupportTicket::route('/create'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
