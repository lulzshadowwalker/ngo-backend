<?php

namespace App\Filament\Cms\Resources;

use App\Enums\ApplicationStatus;
use App\Filament\Cms\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Applications';

    protected static ?string $pluralModelLabel = 'Applications';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organization_id', Auth::user()?->organization_id)
            ->with(['user', 'opportunity', 'applicationForm']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Application Details')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Application Status')
                            ->options(ApplicationStatus::class)
                            ->required()
                            ->reactive(),

                        Forms\Components\DateTimePicker::make('reviewed_at')
                            ->label('Reviewed At')
                            ->visible(fn(callable $get) => $get('status') === ApplicationStatus::Approved->value)
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->visible(fn(callable $get) => $get('status') === ApplicationStatus::Approved->value)
                            ->default(now()),

                        Forms\Components\Textarea::make('notes')
                            ->label('Internal Notes')
                            ->placeholder('Add internal notes about this application...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Application Information')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Applicant')
                            ->disabled(),

                        Forms\Components\TextInput::make('user.email')
                            ->label('Email')
                            ->disabled(),

                        Forms\Components\TextInput::make('opportunity.title')
                            ->label('Opportunity')
                            ->disabled(),

                        Forms\Components\TextInput::make('applicationForm.title')
                            ->label('Form Used')
                            ->disabled(),

                        Forms\Components\TextInput::make('submitted_at')
                            ->label('Submitted')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Forms\Components\Section::make('Application Responses')
                    ->schema([
                        Forms\Components\Repeater::make('responses')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('formField.label')
                                    ->label('Question')
                                    ->disabled(),

                                Forms\Components\Textarea::make('response_value')
                                    ->label('Answer')
                                    ->rows(2)
                                    ->disabled(),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('opportunity.title')
                    ->label('Opportunity')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options(ApplicationStatus::class)
                    ->sortable(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('Not reviewed'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ApplicationStatus::class),

                Tables\Filters\SelectFilter::make('opportunity')
                    ->relationship('opportunity', 'title'),

                Tables\Filters\Filter::make('submitted_today')
                    ->query(fn(Builder $query): Builder => $query->whereDate('submitted_at', today()))
                    ->label('Submitted Today'),

                Tables\Filters\Filter::make('needs_review')
                    ->query(fn(Builder $query): Builder => $query->where('status', ApplicationStatus::Pending))
                    ->label('Needs Review'),
            ])
            ->actions([
                Tables\Actions\Action::make('quick_review')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Quick Review')
                    ->modalSubmitActionLabel('Update Status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(ApplicationStatus::class)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->placeholder('Add notes...')
                            ->rows(3),
                    ])
                    ->action(function (array $data, Application $record) {
                        $record->update([
                            'status' => $data['status'],
                            'notes' => $data['notes'],
                            'reviewed_at' => now(),
                        ]);
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_reviewed')
                        ->label('Mark as Reviewed')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update([
                                'status' => ApplicationStatus::Approved,
                                'reviewed_at' => now(),
                            ]);
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
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
            'index' => Pages\ListApplications::route('/'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
