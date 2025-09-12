<?php

namespace App\Filament\Cms\Resources;

use App\Enums\FormFieldType;
use App\Filament\Cms\Resources\ApplicationFormResource\Pages;
use App\Models\ApplicationForm;
use App\Models\Opportunity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ApplicationFormResource extends Resource
{
    protected static ?string $model = ApplicationForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Application Forms';

    protected static ?string $pluralModelLabel = 'Application Forms';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organization_id', Auth::user()?->organization_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Form Configuration')
                    ->schema([
                        Forms\Components\Placeholder::make('no_opportunities_notice')
                            ->label('')
                            ->content('⚠️ All your opportunities already have application forms. Please create new opportunities first.')
                            ->visible(function () {
                                return Opportunity::where('organization_id', Auth::user()?->organization_id)
                                    ->whereDoesntHave('applicationForm')
                                    ->count() === 0;
                            }),

                        Forms\Components\Select::make('opportunity_id')
                            ->label('Opportunity')
                            ->required()
                            ->options(function () {
                                // Only show opportunities that don't already have an application form
                                $opportunities = Opportunity::where('organization_id', Auth::user()?->organization_id)
                                    ->whereDoesntHave('applicationForm')
                                    ->get()
                                    ->mapWithKeys(fn($opp) => [$opp->id => $opp->getTranslation('title', 'en')]);

                                return $opportunities->count() > 0
                                    ? $opportunities
                                    : ['no_opportunities' => 'No opportunities available - all existing opportunities already have forms'];
                            })
                            ->searchable()
                            ->reactive()
                            ->helperText('Only opportunities without existing forms are shown')
                            ->rules(['required', 'not_in:no_opportunities'])
                            ->validationMessages([
                                'required' => 'Please select an opportunity for this application form.',
                                'not_in' => 'All your opportunities already have application forms. Create a new opportunity first.',
                            ])
                            ->disabled(function () {
                                $availableOpportunities = Opportunity::where('organization_id', Auth::user()?->organization_id)
                                    ->whereDoesntHave('applicationForm')
                                    ->count();
                                return $availableOpportunities === 0;
                            }),

                        Forms\Components\TextInput::make('title')
                            ->label('Form Title')
                            ->required()
                            ->maxLength(255)
                            ->default('Application Form')
                            ->translatable(),

                        Forms\Components\Textarea::make('description')
                            ->label('Form Description')
                            ->rows(3)
                            ->placeholder('Instructions for applicants...')
                            ->translatable(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Form Active')
                            ->helperText('Toggle to enable/disable application submissions')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Form Fields')
                    ->schema([
                        Forms\Components\Repeater::make('formFields')
                            ->relationship()
                            ->label('Form Fields')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Field Type')
                                    ->required()
                                    ->options(FormFieldType::class)
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('options', null)), // Clear options when type changes

                                Forms\Components\TextInput::make('label')
                                    ->label('Field Label')
                                    ->required()
                                    ->maxLength(255)
                                    ->translatable(),

                                Forms\Components\TextInput::make('placeholder')
                                    ->label('Placeholder Text')
                                    ->maxLength(255)
                                    ->translatable(),

                                Forms\Components\Textarea::make('help_text')
                                    ->label('Help Text')
                                    ->rows(2)
                                    ->translatable(),

                                Forms\Components\TagsInput::make('options')
                                    ->label('Options')
                                    ->helperText('For select/checkbox fields - type option and press Enter')
                                    ->visible(function (callable $get) {
                                        $type = $get('type');
                                        if (is_object($type)) {
                                            $typeValue = $type->value;
                                        } else {
                                            $typeValue = $type;
                                        }
                                        return in_array($typeValue, [FormFieldType::Select->value, FormFieldType::Checkbox->value]);
                                    })
                                    ->placeholder('Type an option and press Enter')
                                    ->splitKeys(['Enter', 'Tab', ','])
                                    ->translatable(),

                                Forms\Components\Toggle::make('is_required')
                                    ->label('Required Field')
                                    ->default(false),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Order')
                                    ->numeric()
                                    ->default(0)
                                    ->step(1),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['label']['en'] ?? null)
                            ->addActionLabel('Add Form Field')
                            ->reorderable('sort_order')
                            ->orderColumn('sort_order'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('opportunity.title')
                    ->label('Opportunity')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('formFields_count')
                    ->label('Fields')
                    ->counts('formFields')
                    ->suffix(' fields')
                    ->sortable(),

                Tables\Columns\TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->suffix(' submitted')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\SelectFilter::make('opportunity')
                    ->relationship('opportunity', 'title'),
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
            'index' => Pages\ListApplicationForms::route('/'),
            'create' => Pages\CreateApplicationForm::route('/create'),
            'edit' => Pages\EditApplicationForm::route('/{record}/edit'),
        ];
    }
}
