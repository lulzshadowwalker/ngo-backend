<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Models\Location;
use App\Models\Organization;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

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
        if ($count <= 25) {
            return 'warning';
        }

        return 'success';
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name.' ('.$record->sector?->name.')';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Sector' => $record->sector?->name ?? 'Not specified',
            'Location' => $record->location ? "{$record->location->city}, {$record->location->country}" : 'Not specified',
            'Posts' => $record->posts->count().' posts',
            'Followers' => $record->follows->count().' followers',
            'Created' => $record->created_at->diffForHumans(),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['sector', 'location', 'posts', 'follows']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'bio', 'website', 'sector.name', 'location.city', 'location.country'];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('edit', ['record' => $record]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Organization Details')
                    ->description('Basic organization information and identity')
                    ->aside()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->collection(Organization::MEDIA_COLLECTION_LOGO)
                            ->image()
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->placeholder('Enter organization name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn (string $context, $state, Forms\Set $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->disabled()
                            ->placeholder('Auto-generated from name')
                            ->unique(Organization::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash']),

                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->placeholder('https://organization-website.com')
                            ->prefixIcon('heroicon-o-globe-alt'),
                    ]),

                Forms\Components\Section::make('Classification')
                    ->description('Sector and location information')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('sector_id')
                            ->label('Sector')
                            ->relationship('sector', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select organization sector')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('Enter sector name'),
                                Forms\Components\Textarea::make('description')
                                    ->placeholder('Describe this sector...')
                                    ->rows(3),
                            ]),

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

                Forms\Components\Section::make('About')
                    ->description('Organization description and mission')
                    ->aside()
                    ->schema([
                        Forms\Components\Textarea::make('bio')
                            ->label('Biography/Mission')
                            ->placeholder('Describe your organization, mission, and impact...')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->collection(Organization::MEDIA_COLLECTION_LOGO)
                    ->label('Logo')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Organization name copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Slug copied!')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sector.name')
                    ->label('Sector')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('location.city')
                    ->label('Location')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(
                        fn ($record): string => $record->location ? "{$record->location->city}, {$record->location->country}" : 'Not specified'
                    ),

                Tables\Columns\TextColumn::make('website')
                    ->searchable()
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-globe-alt')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }

                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Posts')
                    ->counts('posts')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('follows_count')
                    ->label('Followers')
                    ->counts('follows')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bio')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
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
                Tables\Filters\SelectFilter::make('sector')
                    ->relationship('sector', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('location')
                    ->relationship('location', 'city')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn (Location $record): string => "{$record->city}, {$record->country}"),

                Tables\Filters\Filter::make('has_website')
                    ->label('Has Website')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('website'))
                    ->toggle(),

                Tables\Filters\Filter::make('popular_organizations')
                    ->label('Popular (10+ Followers)')
                    ->query(
                        fn (Builder $query): Builder => $query->whereHas('follows', function (Builder $query) {
                            $query->havingRaw('COUNT(*) >= 10');
                        })
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('active_organizations')
                    ->label('Active (Has Posts)')
                    ->query(fn (Builder $query): Builder => $query->has('posts'))
                    ->toggle(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('export_organizations')
                        ->label('Export Organizations')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function ($records) {
                            // TODO: Implement export functionality
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Export Organizations')
                        ->modalDescription('Export selected organizations with their details.')
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('send_notification')
                        ->label('Send Notification')
                        ->icon('heroicon-o-bell')
                        ->color('warning')
                        ->action(function ($records) {
                            // TODO: Implement notification functionality
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Send Notification')
                        ->modalDescription('Send a notification to all selected organizations.')
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
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
