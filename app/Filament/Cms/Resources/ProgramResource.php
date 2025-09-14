<?php

namespace App\Filament\Cms\Resources;

use App\Enums\ProgramStatus;
use App\Filament\Cms\Resources\ProgramResource\Pages;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Programs';

    protected static ?string $pluralModelLabel = 'Programs';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('organization_id', Auth::user()?->organization_id)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('organization_id', Auth::user()?->organization_id)->count();
        if ($count === 0) return 'danger';
        if ($count <= 25) return 'warning';
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organization_id', Auth::user()?->organization_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Program Information')
                    ->description('Basic program details and content')
                    ->aside()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->collection(Program::MEDIA_COLLECTION_COVER)
                            ->image()
                            ->columnSpanFull()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->translatable(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(ProgramStatus::class)
                            ->required()
                            ->default(ProgramStatus::Active),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Description')
                    ->description('Detailed program description and objectives')
                    ->aside()
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4)
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
                    ->collection(Program::MEDIA_COLLECTION_COVER)
                    ->label('Cover')
                    ->height(50)
                    ->width(100),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('opportunities_count')
                    ->counts('opportunities')
                    ->label('Opportunities')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->getStateUsing(fn(Program $record): int => views($record)->count())
                    ->label('Views')
                    ->badge()
                    ->icon('heroicon-o-eye')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ProgramStatus::class),
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

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Status' => $record->status->getLabel(),
            'Opportunities' => $record->opportunities_count ?? 0 . ' opportunities',
            'Views' => views($record)->count() . ' views',
            'Created' => $record->created_at?->format('M j, Y') ?? 'Unknown',
        ];
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
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
