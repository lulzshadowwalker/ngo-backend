<?php

namespace App\Filament\Cms\Resources;

use App\Enums\OpportunityStatus;
use App\Filament\Cms\Resources\OpportunityResource\Pages;
use App\Models\Location;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\Sector;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Opportunities';

    protected static ?string $pluralModelLabel = 'Opportunities';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organization_id', Auth::user()?->organization_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->collection(Opportunity::MEDIA_COLLECTION_COVER)
                            ->image()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->translatable(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(3)
                            ->translatable(),

                        Forms\Components\Select::make('program_id')
                            ->label('Program')
                            ->required()
                            ->options(fn() => Program::where('organization_id', Auth::user()?->organization_id)
                                ->get()
                                ->mapWithKeys(fn($program) => [$program->id => $program->getTranslation('title', 'en')]))
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->translatable(),
                                Forms\Components\Textarea::make('description')
                                    ->required()
                                    ->translatable(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['organization_id'] = Auth::user()->organization_id;
                                return Program::create($data)->getKey();
                            }),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(OpportunityStatus::class)
                            ->required()
                            ->default(OpportunityStatus::Active),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Opportunity Details')
                    ->schema([
                        Forms\Components\Textarea::make('about_the_role')
                            ->label('About the Role')
                            ->rows(3)
                            ->translatable(),

                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->helperText('Enter relevant tags for this opportunity (max 20 tags)')
                            ->separator(',')
                            ->splitKeys(['Tab', 'Enter', ','])
                            ->translatable(),

                        Forms\Components\TextInput::make('duration')
                            ->label('Duration')
                            ->numeric()
                            ->suffix('Days')
                            ->helperText('Duration in days (1-365 days)')
                            ->minValue(1)
                            ->maxValue(365)
                            ->step(1),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('Application Deadline')
                            ->minDate(now()->addDays(1))
                            ->helperText('Deadline must be in the future')
                            ->displayFormat('M d, Y'),

                        Forms\Components\Select::make('sector_id')
                            ->label('Sector')
                            ->options(Sector::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Requirements & Responsibilities')
                    ->schema([
                        Forms\Components\TagsInput::make('key_responsibilities')
                            ->label('Key Responsibilities')
                            ->helperText('Enter each responsibility as a separate item (max 10 items)')
                            ->translatable(),

                        Forms\Components\TagsInput::make('required_skills')
                            ->label('Required Skills')
                            ->helperText('Enter each skill as a separate item (max 15 items)')
                            ->translatable(),

                        Forms\Components\TagsInput::make('time_commitment')
                            ->label('Time Commitment')
                            ->helperText('e.g., "5 hours per week", "3 months minimum", etc.')
                            ->translatable(),

                        Forms\Components\TagsInput::make('benefits')
                            ->label('Benefits')
                            ->helperText('Enter each benefit as a separate item (max 10 items)')
                            ->translatable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location Information')
                    ->schema([
                        Forms\Components\Select::make('location_id')
                            ->label('Location')
                            ->options(Location::selectRaw("id, CONCAT(city, ', ', country) as location")
                                ->pluck('location', 'id'))
                            ->searchable(),

                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.00000001)
                            ->minValue(-90)
                            ->maxValue(90)
                            ->placeholder('e.g., 31.9539')
                            ->helperText('Latitude coordinate (-90 to 90)'),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.00000001)
                            ->minValue(-180)
                            ->maxValue(180)
                            ->placeholder('e.g., 35.9106')
                            ->helperText('Longitude coordinate (-180 to 180)'),

                        Forms\Components\Textarea::make('location_description')
                            ->label('Location Description')
                            ->placeholder('e.g., Environmental Center, King Hussein Park, Amman, Jordan')
                            ->rows(2)
                            ->columnSpanFull()
                            ->translatable(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('extra')
                            ->label('Extra Information')
                            ->helperText('Any additional information you want to include')
                            ->columnSpanFull()
                            ->translatable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->collection(Opportunity::MEDIA_COLLECTION_COVER)
                    ->label('Cover')
                    ->height(50)
                    ->width(100),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('program.title')
                    ->label('Program')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sector.name')
                    ->label('Sector')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->sortable()
                    ->suffix(' days')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Deadline')
                    ->date('M j, Y')
                    ->sortable()
                    ->color(fn(Opportunity $record) => $record->expiry_date?->isPast() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('views_count')
                    ->getStateUsing(fn(Opportunity $record): int => views($record)->count())
                    ->badge()
                    ->icon('heroicon-o-eye')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OpportunityStatus::class),

                Tables\Filters\SelectFilter::make('program')
                    ->relationship('program', 'title'),

                Tables\Filters\SelectFilter::make('sector')
                    ->relationship('sector', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOpportunities::route('/'),
            'create' => Pages\CreateOpportunity::route('/create'),
            'edit' => Pages\EditOpportunity::route('/{record}/edit'),
        ];
    }
}
