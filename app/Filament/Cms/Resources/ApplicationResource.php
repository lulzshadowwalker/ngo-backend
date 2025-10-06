<?php

namespace App\Filament\Cms\Resources;

use App\Enums\ApplicationStatus;
use App\Enums\FormFieldType;
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

    protected static ?string $navigationGroup = 'Application Management';

    protected static ?string $navigationLabel = 'Applications';

    protected static ?string $pluralModelLabel = 'Applications';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('organization_id', Auth::user()?->organization_id)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('organization_id', Auth::user()?->organization_id)->count();
        if ($count === 0) {
            return 'danger';
        }
        if ($count <= 25) {
            return 'warning';
        }

        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organization_id', Auth::user()?->organization_id)
            ->with(['user', 'opportunity', 'applicationForm', 'responses.formField']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Application Details')
                    ->description('Application status and review information')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Application Status')
                            ->options(ApplicationStatus::class)
                            ->required()
                            ->columnSpanFull()
                            ->reactive(),

                        Forms\Components\DateTimePicker::make('reviewed_at')
                            ->label('Reviewed At')
                            ->columnSpanFull()
                            ->visible(fn (callable $get) => $get('status') === ApplicationStatus::Approved->value)
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->columnSpanFull()
                            ->visible(fn (callable $get) => $get('status') === ApplicationStatus::Approved->value)
                            ->default(now()),

                        Forms\Components\Textarea::make('notes')
                            ->label('Internal Notes')
                            ->placeholder('Add internal notes about this application...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Application Information')
                    ->description('Read-only application and applicant details')
                    ->aside()
                    ->schema([
                        Forms\Components\Placeholder::make('applicant_name')
                            ->label('Applicant')
                            ->content(fn ($record) => $record?->user?->name ?? 'No applicant'),

                        Forms\Components\Placeholder::make('applicant_email')
                            ->label('Email')
                            ->content(fn ($record) => $record?->user?->email ?? 'No email'),

                        Forms\Components\Placeholder::make('opportunity_title')
                            ->label('Opportunity')
                            ->content(fn ($record) => $record?->opportunity?->getTranslation('title', 'en') ?? 'No opportunity'),

                        Forms\Components\Placeholder::make('form_title')
                            ->label('Form Used')
                            ->content(fn ($record) => $record?->applicationForm?->getTranslation('title', 'en') ?? 'No form'),

                        Forms\Components\Placeholder::make('submitted_date')
                            ->label('Submitted')
                            ->content(fn ($record) => $record?->submitted_at?->format('M j, Y g:i A') ?? 'Not submitted'),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Application Responses')
                    ->description('Applicant responses to form fields')
                    ->aside()
                    ->schema([
                        Forms\Components\Repeater::make('responses')
                            ->relationship()
                            ->columnSpanFull()
                            ->schema([
                                Forms\Components\Placeholder::make('question')
                                    ->label('Question')
                                    ->content(fn ($record) => $record?->formField?->getTranslation('label', 'en') ?? 'No question'),

                                Forms\Components\Placeholder::make('answer')
                                    ->label('Answer')
                                    ->content(function ($record) {
                                        if (! $record?->value && ! $record?->file_path) {
                                            return 'No answer';
                                        }

                                        // Handle file uploads - check if it's a file type field
                                        if ($record?->formField?->type === FormFieldType::File) {
                                            // Check if file_path exists (preferred) or value contains filename
                                            $filePath = $record->file_path ?: $record->value;

                                            if ($filePath) {
                                                $fileName = basename($filePath);
                                                $downloadUrl = route('application.download-file', [
                                                    'application' => $record->application_id,
                                                    'response' => $record->id,
                                                ]);

                                                return new \Illuminate\Support\HtmlString(
                                                    '<div class="flex items-center space-x-2">'.
                                                        '<span class="text-sm text-gray-600">File uploaded:</span>'.
                                                        '<a href="'.$downloadUrl.'" target="_blank" '.
                                                        'class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-primary-600 rounded-md hover:bg-primary-500 transition-colors">'.
                                                        '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">'.
                                                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>'.
                                                        '</svg>'.
                                                        'Download '.$fileName.
                                                        '</a>'.
                                                        '</div>'
                                                );
                                            } else {
                                                return 'File field but no file uploaded';
                                            }
                                        }

                                        // Handle regular responses
                                        if ($record?->value) {
                                            // Handle array values (for checkboxes, etc.)
                                            if (is_array($record->value)) {
                                                return implode(', ', $record->value);
                                            }

                                            return (string) $record->value;
                                        }

                                        return 'No answer';
                                    }),
                            ])
                            ->columns(1)
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
                    ->limit(30)
                    ->toggleable(),

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
                    ->placeholder('Not reviewed')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ApplicationStatus::class),

                Tables\Filters\SelectFilter::make('opportunity')
                    ->relationship('opportunity', 'title'),

                Tables\Filters\Filter::make('submitted_today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('submitted_at', today()))
                    ->label('Submitted Today'),

                Tables\Filters\Filter::make('needs_review')
                    ->query(fn (Builder $query): Builder => $query->where('status', ApplicationStatus::Pending))
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

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return ($record->user?->name ?? 'Unknown User').' - '.($record->opportunity?->title ?? 'Unknown Opportunity');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Applicant' => $record->user?->name ?? 'Unknown User',
            'Email' => $record->user?->email ?? 'No email',
            'Opportunity' => $record->opportunity?->title ?? 'Unknown',
            'Status' => $record->status->getLabel(),
            'Submitted' => $record->submitted_at?->format('M j, Y g:i A') ?? 'Unknown',
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
            'index' => Pages\ListApplications::route('/'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
