<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\IndividualResource\Pages;
use App\Models\Individual;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IndividualResource extends Resource
{
    protected static ?string $model = Individual::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();

        if ($count === 0) {
            return 'danger';
        }
        if ($count <= 50) {
            return 'warning';
        }

        return 'success';
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->user->name.' ('.$record->user->email.')';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->user->email,
            'Location' => $record->location?->city.', '.$record->location?->country,
            'Skills' => $record->skills->count().' skills',
            'Joined' => $record->created_at->diffForHumans(),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'location', 'skills']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'user.email', 'bio', 'location.city', 'location.country'];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('edit', ['record' => $record]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('Basic user account details and contact information')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User Account')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => "{$record->name} ({$record->email})")
                            ->placeholder('Select a user account')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('Enter full name'),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->placeholder('Enter email address'),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->placeholder('Enter password'),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $user = User::create($data);
                                $user->assignRole(Role::individual->value);

                                return $user->id;
                            }),

                        Forms\Components\Select::make('location_id')
                            ->label('Location')
                            ->relationship('location', 'city')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (Location $record): string => "{$record->city}, {$record->country}")
                            ->placeholder('Select location')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('city')
                                    ->required()
                                    ->placeholder('Enter city name'),
                                Forms\Components\TextInput::make('country')
                                    ->required()
                                    ->placeholder('Enter country name'),
                            ]),
                    ]),

                Forms\Components\Section::make('Personal Information')
                    ->description('Individual profile details and personal information')
                    ->aside()
                    ->schema([
                        Forms\Components\DatePicker::make('birthdate')
                            ->label('Date of Birth')
                            ->placeholder('Select birth date')
                            ->maxDate(now()->subYears(13))
                            ->displayFormat('M j, Y'),

                        Forms\Components\Textarea::make('bio')
                            ->label('Biography')
                            ->placeholder('Tell us about yourself, your experience, and what motivates you...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Skills & Interests')
                    ->description('Professional skills and volunteering interests')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('skills')
                            ->relationship('skills', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select relevant skills')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('Enter skill name'),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('sectors')
                            ->label('Volunteering Interests')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('Enter area of interest'),
                            ])
                            ->addActionLabel('Add Interest')
                            ->columnSpanFull()
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatarFile')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->user->name)),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Name copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('location.city')
                    ->label('Location')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record): string => $record->location ? "{$record->location->city}, {$record->location->country}" : 'Not specified'
                    ),

                Tables\Columns\TextColumn::make('skills')
                    ->label('Skills')
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->getStateUsing(fn ($record) => $record->skills->pluck('name')),

                Tables\Columns\TextColumn::make('birthdate')
                    ->label('Age')
                    ->date()
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => $state ? Carbon::parse($state)->age.' years old' : 'Not specified'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
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
                Tables\Filters\SelectFilter::make('location')
                    ->relationship('location', 'city')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn (Location $record): string => "{$record->city}, {$record->country}"),

                Tables\Filters\SelectFilter::make('skills')
                    ->relationship('skills', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Tables\Filters\Filter::make('age_range')
                    ->form([
                        Forms\Components\TextInput::make('min_age')
                            ->numeric()
                            ->placeholder('Minimum age'),
                        Forms\Components\TextInput::make('max_age')
                            ->numeric()
                            ->placeholder('Maximum age'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_age'],
                                fn (Builder $query, $age): Builder => $query->whereDate('birthdate', '<=', now()->subYears($age)),
                            )
                            ->when(
                                $data['max_age'],
                                fn (Builder $query, $age): Builder => $query->whereDate('birthdate', '>=', now()->subYears($age)),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['min_age'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Min age: '.$data['min_age'])
                                ->removeField('min_age');
                        }

                        if ($data['max_age'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Max age: '.$data['max_age'])
                                ->removeField('max_age');
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Joined from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Joined until'),
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
                            $indicators[] = Tables\Filters\Indicator::make('Joined from '.Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Joined until '.Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view_profile')
                        ->label('View Profile')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (Individual $record): string => '#') // TODO: Add profile URL when available
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('send_message')
                        ->label('Send Message')
                        ->icon('heroicon-o-chat-bubble-left')
                        ->color('success')
                        ->action(function (Individual $record) {
                            // TODO: Implement messaging functionality
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Send Message')
                        ->modalDescription('This feature will be implemented soon.'),

                    Tables\Actions\DeleteAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('export_contacts')
                        ->label('Export Contacts')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function ($records) {
                            // TODO: Implement export functionality
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Export Contacts')
                        ->modalDescription('Export contact information for selected individuals.')
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
            'index' => Pages\ListIndividuals::route('/'),
            'create' => Pages\CreateIndividual::route('/create'),
            'edit' => Pages\EditIndividual::route('/{record}/edit'),
        ];
    }
}
