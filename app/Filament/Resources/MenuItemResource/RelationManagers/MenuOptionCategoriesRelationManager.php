<?php

namespace App\Filament\Resources\MenuItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use App\Models\MenuOptionCategory;

class MenuOptionCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'optionCategories';

    protected static ?string $title = 'Option Categories';

    protected static ?string $modelLabel = 'Option Category';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id')
                    ->label('Option Category')
                    ->options(MenuOptionCategory::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Toggle::make('is_required')
                    ->label('Required for this menu item')
                    ->default(false),
                    
                Forms\Components\TextInput::make('sort_order')
                    ->label('Display Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Order in which this category appears for this menu item'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->placeholder('-'),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'single',
                        'success' => 'multiple',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'single' => 'Single Choice',
                        'multiple' => 'Multiple Choice',
                        default => ucfirst($state),
                    }),

                Tables\Columns\IconColumn::make('pivot.is_required')
                    ->label('Required')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('options_count')
                    ->label('Options Count')
                    ->counts('menuOptions')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
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

                Tables\Filters\TernaryFilter::make('pivot.is_required')
                    ->label('Required')
                    ->boolean()
                    ->trueLabel('Required only')
                    ->falseLabel('Optional only')
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Attach Category')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_required')
                            ->label('Required for this menu item')
                            ->default(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->modalHeading('Attach Option Category')
                    ->successNotificationTitle('Option category attached successfully'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Toggle::make('is_required')
                            ->label('Required for this menu item'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric(),
                    ])
                    ->modalHeading('Edit Category Settings'),

                Tables\Actions\DetachAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove Option Category')
                    ->modalDescription('This will remove this option category from this menu item. The category itself will not be deleted.')
                    ->successNotificationTitle('Option category removed successfully'),

                Tables\Actions\Action::make('manage_options')
                    ->label('Manage Options')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('info')
                    ->url(fn ($record): string => 
                        route('filament.admin.resources.menu-option-categories.edit', 
                            ['record' => $record->id]
                        )
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('view_options')
                    ->label('View Options')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Options for: ' . $record->name)
                    ->modalContent(function ($record) {
                        $options = $record->menuOptions()->orderBy('sort_order')->get();
                        
                        if ($options->isEmpty()) {
                            return view('filament.components.empty-state', [
                                'message' => 'No options available for this category.'
                            ]);
                        }
                        
                        return view('filament.components.option-list', [
                            'options' => $options,
                            'category' => $record
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Remove Selected')
                        ->modalHeading('Remove Option Categories')
                        ->modalDescription('This will remove the selected option categories from this menu item.')
                        ->successNotificationTitle('Option categories removed successfully'),
                        
                    Tables\Actions\BulkAction::make('update_requirement')
                        ->label('Update Requirement')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->form([
                            Forms\Components\Select::make('is_required')
                                ->label('Required Status')
                                ->options([
                                    true => 'Required',
                                    false => 'Optional',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->pivot->update(['is_required' => $data['is_required']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Requirement status updated'),

                    Tables\Actions\BulkAction::make('update_sort_order')
                        ->label('Update Display Order')
                        ->icon('heroicon-o-bars-3')
                        ->form([
                            Forms\Components\TextInput::make('sort_order')
                                ->label('Display Order')
                                ->numeric()
                                ->required()
                                ->helperText('All selected categories will be assigned this order value'),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->pivot->update(['sort_order' => $data['sort_order']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Display order updated'),
                ]),
            ])
            ->defaultSort('menu_item_option_categories.sort_order')
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('menu_item_option_categories.sort_order'))
            ->emptyStateHeading('No option categories')
            ->emptyStateDescription('Add option categories to allow customers to customize this menu item.')
            ->emptyStateIcon('heroicon-o-squares-plus')
            ->emptyStateActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add First Category')
                    ->button(),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}