<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Order Items';

    protected static ?string $modelLabel = 'Order Item';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('menu_item_id')
                            ->relationship('menuItem', 'name', 
                                fn (Builder $query) => $query->where('restaurant_id', $this->ownerRecord->restaurant_id)
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $menuItem = \App\Models\MenuItem::find($state);
                                    if ($menuItem) {
                                        $set('unit_price', $menuItem->price);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $quantity = intval($get('quantity') ?? 1);
                                $unitPrice = floatval($get('unit_price') ?? 0);
                                $set('total_price', $quantity * $unitPrice);
                            }),

                        Forms\Components\TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $quantity = intval($get('quantity') ?? 1);
                                $unitPrice = floatval($get('unit_price') ?? 0);
                                $set('total_price', $quantity * $unitPrice);
                            }),

                        Forms\Components\TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'preparing' => 'Preparing',
                                'ready' => 'Ready',
                                'served' => 'Served',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\Placeholder::make('timestamps_placeholder')
                            ->label('')
                            ->content(''),
                    ]),

                Forms\Components\Textarea::make('special_instructions')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Any special instructions for this item...'),

                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at')
                            ->displayFormat('d/m/Y H:i')
                            ->visible(fn (callable $get) => in_array($get('status'), ['preparing', 'ready', 'served'])),

                        Forms\Components\DateTimePicker::make('ready_at')
                            ->displayFormat('d/m/Y H:i')
                            ->visible(fn (callable $get) => in_array($get('status'), ['ready', 'served'])),

                        Forms\Components\DateTimePicker::make('served_at')
                            ->displayFormat('d/m/Y H:i')
                            ->visible(fn (callable $get) => $get('status') === 'served'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('menuItem.name')
            ->columns([
                Tables\Columns\TextColumn::make('menuItem.name')
                    ->label('Item')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR')
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'info' => 'preparing',
                        'success' => 'ready',
                        'success' => 'served',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('special_instructions')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->default('-'),

                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime('H:i')
                    ->sortable()
                    ->toggleable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('ready_at')
                    ->dateTime('H:i')
                    ->sortable()
                    ->toggleable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('served_at')
                    ->dateTime('H:i')
                    ->sortable()
                    ->toggleable()
                    ->default('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'preparing' => 'Preparing',
                        'ready' => 'Ready',
                        'served' => 'Served',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('menu_item_id')
                    ->relationship('menuItem', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Calculate total price if not already set
                        if (!isset($data['total_price']) || $data['total_price'] == 0) {
                            $data['total_price'] = $data['quantity'] * $data['unit_price'];
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Recalculate total price
                        $data['total_price'] = $data['quantity'] * $data['unit_price'];
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'preparing' => 'Preparing',
                                'ready' => 'Ready',
                                'served' => 'Served',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $updateData = ['status' => $data['status']];
                        
                        // Auto-update timestamps based on status
                        if ($data['status'] === 'preparing' && !$record->started_at) {
                            $updateData['started_at'] = now();
                        } elseif ($data['status'] === 'ready' && !$record->ready_at) {
                            $updateData['ready_at'] = now();
                            if (!$record->started_at) {
                                $updateData['started_at'] = now()->subMinutes(5);
                            }
                        } elseif ($data['status'] === 'served' && !$record->served_at) {
                            $updateData['served_at'] = now();
                            if (!$record->ready_at) {
                                $updateData['ready_at'] = now()->subMinutes(2);
                            }
                            if (!$record->started_at) {
                                $updateData['started_at'] = now()->subMinutes(7);
                            }
                        }
                        
                        $record->update($updateData);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'preparing' => 'Preparing',
                                    'ready' => 'Ready',
                                    'served' => 'Served',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $updateData = ['status' => $data['status']];
                                
                                // Auto-update timestamps
                                if ($data['status'] === 'preparing' && !$record->started_at) {
                                    $updateData['started_at'] = now();
                                } elseif ($data['status'] === 'ready' && !$record->ready_at) {
                                    $updateData['ready_at'] = now();
                                } elseif ($data['status'] === 'served' && !$record->served_at) {
                                    $updateData['served_at'] = now();
                                }
                                
                                $record->update($updateData);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('id')
            ->poll('30s');
    }
}