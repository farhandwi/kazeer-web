<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuOptionCategoryResource\Pages;
use App\Filament\Resources\MenuOptionCategoryResource\RelationManagers;
use App\Models\MenuOptionCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\Tabs;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Get;
use Filament\Forms\Set;

class MenuOptionCategoryResource extends Resource
{
    protected static ?string $model = MenuOptionCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Option Categories';

    protected static ?string $modelLabel = 'Option Category';

    protected static ?string $pluralModelLabel = 'Option Categories';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Menu Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => 
                                        $context === 'edit' ? null : $set('slug', Str::slug($state))
                                    ),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->rules(['regex:/^[a-z0-9\-]+$/']),

                                Forms\Components\Select::make('type')
                                    ->options([
                                        'single' => 'Single Selection (Radio Button)',
                                        'multiple' => 'Multiple Selection (Checkbox)',
                                    ])
                                    ->default('single')
                                    ->required()
                                    ->helperText('Single: Customer can only choose one option. Multiple: Customer can choose multiple options.'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Display Order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Lower numbers appear first'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Optional description to help customers understand this option category'),

                        Forms\Components\Toggle::make('is_required')
                            ->label('Required by Default')
                            ->default(false)
                            ->helperText('This can be overridden per menu item'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'info' => 'single',
                        'success' => 'multiple',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'single' => 'Single Choice',
                        'multiple' => 'Multiple Choice',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('options_count')
                    ->label('Options')
                    ->counts('options')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('menu_items_count')
                    ->label('Used in Items')
                    ->counts('menuItems')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'single' => 'Single Choice',
                        'multiple' => 'Multiple Choice',
                    ]),

                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('Required')
                    ->boolean()
                    ->trueLabel('Required only')
                    ->falseLabel('Optional only')
                    ->native(false),

                Tables\Filters\Filter::make('has_options')
                    ->label('With Options')
                    ->query(fn (Builder $query): Builder => $query->has('options'))
                    ->toggle(),

                Tables\Filters\Filter::make('no_options')
                    ->label('Without Options')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('options'))
                    ->toggle(),

                Tables\Filters\Filter::make('used_in_menu')
                    ->label('Used in Menu Items')
                    ->query(fn (Builder $query): Builder => $query->has('menuItems'))
                    ->toggle(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (MenuOptionCategory $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $record->name . ' (Copy)';
                        $newRecord->slug = $record->slug . '-copy';
                        $newRecord->save();
                        
                        // Copy options
                        foreach ($record->options as $option) {
                            $newOption = $option->replicate();
                            $newOption->option_category_id = $newRecord->id;
                            $newOption->save();
                        }
                        
                        return redirect()->route('filament.admin.resources.menu-option-categories.edit', $newRecord);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Option Category')
                    ->modalDescription('This will create a copy of this category with all its options.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('update_type')
                        ->label('Change Type')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->form([
                            Forms\Components\Select::make('type')
                                ->options([
                                    'single' => 'Single Choice',
                                    'multiple' => 'Multiple Choice',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['type' => $data['type']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('update_required')
                        ->label('Set Required Status')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\Select::make('is_required')
                                ->label('Required Status')
                                ->options([
                                    true => 'Required',
                                    false => 'Optional',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['is_required' => $data['is_required']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->weight(FontWeight::Bold)
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                Infolists\Components\TextEntry::make('slug')
                                    ->copyable(),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'single' => 'info',
                                        'multiple' => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'single' => 'Single Choice',
                                        'multiple' => 'Multiple Choice',
                                        default => ucfirst($state),
                                    }),

                                Infolists\Components\TextEntry::make('sort_order')
                                    ->label('Display Order'),

                                Infolists\Components\IconEntry::make('is_required')
                                    ->label('Required by Default')
                                    ->boolean(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Options')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('options')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('formatted_additional_price')
                                            ->label('Price'),
                                        Infolists\Components\IconEntry::make('is_available')
                                            ->boolean()
                                            ->label('Available'),
                                        Infolists\Components\TextEntry::make('sort_order')
                                            ->label('Order'),
                                    ]),
                                Infolists\Components\TextEntry::make('description')
                                    ->placeholder('No description')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->visible(fn (MenuOptionCategory $record): bool => $record->options->isNotEmpty()),

                        Infolists\Components\TextEntry::make('no_options')
                            ->label('Options')
                            ->getStateUsing(fn (): string => 'No options configured yet')
                            ->visible(fn (MenuOptionCategory $record): bool => $record->options->isEmpty()),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Usage Statistics')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('options_count')
                                    ->label('Total Options')
                                    ->getStateUsing(fn (MenuOptionCategory $record): int => $record->options->count()),

                                Infolists\Components\TextEntry::make('menu_items_count')
                                    ->label('Used in Menu Items')
                                    ->getStateUsing(fn (MenuOptionCategory $record): int => $record->menuItems->count()),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime('d/m/Y H:i'),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MenuOptionsRelationManager::class,
            RelationManagers\MenuItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuOptionCategories::route('/'),
            'create' => Pages\CreateMenuOptionCategory::route('/create'),
            'view' => Pages\ViewMenuOptionCategory::route('/{record}'),
            'edit' => Pages\EditMenuOptionCategory::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'slug'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Type' => $record->type === 'single' ? 'Single Choice' : 'Multiple Choice',
            'Options' => $record->options->count() . ' options',
            'Required' => $record->is_required ? 'Required' : 'Optional',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}