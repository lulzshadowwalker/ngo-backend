<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VolunteeringInterestResource\Pages;
use App\Models\VolunteeringInterest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VolunteeringInterestResource extends Resource
{
    protected static ?string $model = VolunteeringInterest::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Taxonomies';

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
        if ($count < 20) {
            return 'warning';
        }

        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Volunteering Interest Details')
                    ->description('Manage the name and translation of a volunteering interest.')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Interest Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Environmental Protection')
                            ->translatable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('individuals_count')
                    ->counts('individuals')
                    ->label('Used By')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListVolunteeringInterests::route('/'),
            'create' => Pages\CreateVolunteeringInterest::route('/create'),
            'edit' => Pages\EditVolunteeringInterest::route('/{record}/edit'),
        ];
    }
}
