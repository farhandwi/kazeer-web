<?php

namespace App\Filament\Resources\MenuOptionCategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Str;

class MenuOptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'menuOptions';

    protected static ?string $title = 'Options';

    protected static ?string $modelLabel = 'Option';

    public function form(Form $form): Form
    {
        return $form
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
                            ->rules(['regex:/^[a-z0-9\-]+$/'])
                            ->helperText('URL-friendly version of the name'),
                    ]),

                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Optional description for this option...'),

                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('additional_price')
                            ->label('Additional Price')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->step(0.01)
                            ->helperText('Leave 0 for free options, use negative values for discounts'),

                        Forms\Components\Toggle::make('is_available')
                            ->label('Available')
                            ->default(true)
                            ->helperText('Uncheck to temporarily hide this option'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->color('gray')
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    })
                    ->default('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('additional_price')
                    ->label('Price')
                    ->formatStateUsing(function ($state) {
                        if ($state == 0) {
                            return 'Free';
                        }
                        $prefix = $state > 0 ? '+' : '';
                        return $prefix . 'Rp ' . number_format($state, 0, ',', '.');
                    })
                    ->color(fn ($state) => match (true) {
                        $state > 0 => 'success',
                        $state < 0 => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueLabel('Available only')
                    ->falseLabel('Unavailable only')
                    ->native(false),

                Tables\Filters\Filter::make('free_options')
                    ->label('Free Options')
                    ->query(fn (Builder $query): Builder => $query->where('additional_price', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('paid_options')
                    ->label('Paid Options')
                    ->query(fn (Builder $query): Builder => $query->where('additional_price', '>', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('discount_options')
                    ->label('Discount Options')
                    ->query(fn (Builder $query): Builder => $query->where('additional_price', '<', 0))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Option')
                    ->modalHeading('Add New Option')
                    ->successNotificationTitle('Option created successfully'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Option'),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Delete Option')
                    ->modalDescription('Are you sure you want to delete this option? This action cannot be undone.'),
                Tables\Actions\Action::make('toggle_availability')
                    ->label(fn ($record) => $record->is_available ? 'Mark Unavailable' : 'Mark Available')
                    ->icon(fn ($record) => $record->is_available ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->is_available ? 'warning' : 'success')
                    ->action(fn ($record) => $record->update(['is_available' => !$record->is_available]))
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_available ? 'Mark as Unavailable' : 'Mark as Available')
                    ->modalDescription(fn ($record) => 
                        $record->is_available 
                            ? 'This will hide the option from customers.' 
                            : 'This will make the option available to customers.'
                    ),
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function ($record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $record->name . ' (Copy)';
                        $newRecord->slug = $record->slug . '-copy';
                        
                        // Ensure unique slug
                        $baseSlug = $newRecord->slug;
                        $counter = 1;
                        while (\App\Models\MenuOption::where('slug', $newRecord->slug)->exists()) {
                            $newRecord->slug = $baseSlug . '-' . $counter;
                            $counter++;
                        }
                        
                        $newRecord->save();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Option duplicated successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Option')
                    ->modalDescription('This will create a copy of this option.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggle_availability')
                        ->label('Toggle Availability')
                        ->icon('heroicon-o-eye')
                        ->form([
                            Forms\Components\Select::make('is_available')
                                ->label('Availability')
                                ->options([
                                    true => 'Available',
                                    false => 'Unavailable',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['is_available' => $data['is_available']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('update_pricing')
                        ->label('Update Pricing')
                        ->icon('heroicon-o-banknotes')
                        ->form([
                            Forms\Components\Select::make('pricing_action')
                                ->label('Action')
                                ->options([
                                    'set' => 'Set specific price',
                                    'increase' => 'Increase price by amount',
                                    'decrease' => 'Decrease price by amount',
                                    'percentage_increase' => 'Increase by percentage',
                                    'percentage_decrease' => 'Decrease by percentage',
                                ])
                                ->required()
                                ->live(),
                            Forms\Components\TextInput::make('amount')
                                ->label(fn (callable $get) => match($get('pricing_action')) {
                                    'set' => 'New Price',
                                    'increase', 'decrease' => 'Amount',
                                    'percentage_increase', 'percentage_decrease' => 'Percentage',
                                    default => 'Amount',
                                })
                                ->numeric()
                                ->required()
                                ->prefix(fn (callable $get) => in_array($get('pricing_action'), ['percentage_increase', 'percentage_decrease']) ? '' : 'Rp')
                                ->suffix(fn (callable $get) => in_array($get('pricing_action'), ['percentage_increase', 'percentage_decrease']) ? '%' : ''),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $currentPrice = $record->additional_price;
                                $newPrice = match($data['pricing_action']) {
                                    'set' => $data['amount'],
                                    'increase' => $currentPrice + $data['amount'],
                                    'decrease' => $currentPrice - $data['amount'],
                                    'percentage_increase' => $currentPrice + ($currentPrice * $data['amount'] / 100),
                                    'percentage_decrease' => $currentPrice - ($currentPrice * $data['amount'] / 100),
                                    default => $currentPrice,
                                };
                                
                                $record->update(['additional_price' => max(0, $newPrice)]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->emptyStateHeading('No options yet')
            ->emptyStateDescription('Add options that customers can choose from this category.')
            ->emptyStateIcon('heroicon-o-plus-circle')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add First Option')
                    ->button(),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}