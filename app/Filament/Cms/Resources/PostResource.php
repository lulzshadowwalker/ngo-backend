<?php

namespace App\Filament\Cms\Resources;

use App\Filament\Cms\Resources\PostResource\Pages;
use App\Filament\Cms\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('cover')
                    ->collection(Post::MEDIA_COLLECTION_COVER)
                    ->image()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->translatable(),
                Forms\Components\MarkdownEditor::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->translatable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->collection(Post::MEDIA_COLLECTION_COVER)
                    ->label('Cover')
                    ->height(50)
                    ->width(100),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('views_count')
                    ->getStateUsing(fn(Post $record): int => views($record)->count())
                    ->alignRight()
                    ->badge()
                    ->icon('heroicon-o-eye')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Published At')
                    ->description(fn(Post $record): string => $record->created_at?->diffForHumans() ?? '')
                    ->alignRight()
                    ->dateTime()
                    ->sortable(),
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
