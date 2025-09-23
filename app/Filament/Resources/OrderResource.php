<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Customer;
use App\Models\Discount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table as FilamentTable;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\Tabs;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?string $modelLabel = 'Order';

    protected static ?string $pluralModelLabel = 'Orders';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Order Details')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('order_number')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->default(fn () => 'ORD-' . strtoupper(uniqid()))
                                            ->disabled()
                                            ->dehydrated(),

                                        Forms\Components\Select::make('restaurant_id')
                                            ->relationship('restaurant', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => $set('table_id', null)),

                                        Forms\Components\Select::make('table_id')
                                            ->relationship('table', 'table_number', 
                                                fn (Builder $query, callable $get) => $query->where('restaurant_id', $get('restaurant_id'))
                                            )
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->disabled(fn (callable $get) => !$get('restaurant_id')),

                                        Forms\Components\Select::make('customer_id')
                                            ->relationship('customer', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('email')
                                                    ->email()
                                                    ->unique()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('phone')
                                                    ->tel()
                                                    ->maxLength(255),
                                            ]),

                                        Forms\Components\TextInput::make('customer_name')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('customer_phone')
                                            ->tel()
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->options([
                                                'pending' => 'Pending',
                                                'confirmed' => 'Confirmed',
                                                'preparing' => 'Preparing',
                                                'ready' => 'Ready',
                                                'served' => 'Served',
                                                'completed' => 'Completed',
                                                'cancelled' => 'Cancelled',
                                            ])
                                            ->default('pending')
                                            ->required()
                                            ->live(),

                                        Forms\Components\Select::make('payment_status')
                                            ->options([
                                                'pending' => 'Pending',
                                                'paid' => 'Paid',
                                                'failed' => 'Failed',
                                                'refunded' => 'Refunded',
                                            ])
                                            ->default('pending')
                                            ->required(),

                                        Forms\Components\Select::make('payment_method')
                                            ->options([
                                                'cash' => 'Cash',
                                                'card' => 'Card',
                                                'digital_wallet' => 'Digital Wallet',
                                                'transfer' => 'Transfer',
                                            ]),

                                        Forms\Components\TextInput::make('estimated_prep_time')
                                            ->numeric()
                                            ->suffix('minutes'),
                                    ]),

                                Forms\Components\Textarea::make('special_instructions')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Pricing & Discount')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('discount_id')
                                            ->relationship('discount', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                if ($state) {
                                                    $discount = Discount::find($state);
                                                    if ($discount) {
                                                        $set('discount_code', $discount->code);
                                                        $set('discount_details', json_encode([
                                                            'type' => $discount->type,
                                                            'value' => $discount->value,
                                                            'name' => $discount->name
                                                        ]));
                                                    }
                                                } else {
                                                    $set('discount_code', null);
                                                    $set('discount_details', null);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('discount_code')
                                            ->maxLength(255)
                                            ->disabled(),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('subtotal')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                self::calculateTotal($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('tax_amount')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                self::calculateTotal($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('service_charge')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                self::calculateTotal($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('discount_amount')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->live()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                self::calculateTotal($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('total_amount')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Hidden::make('discount_details'),
                            ]),

                        Tabs\Tab::make('Timestamps')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('confirmed_at')
                                            ->displayFormat('d/m/Y H:i')
                                            ->visible(fn (callable $get) => in_array($get('status'), ['confirmed', 'preparing', 'ready', 'served', 'completed'])),

                                        Forms\Components\DateTimePicker::make('ready_at')
                                            ->displayFormat('d/m/Y H:i')
                                            ->visible(fn (callable $get) => in_array($get('status'), ['ready', 'served', 'completed'])),

                                        Forms\Components\DateTimePicker::make('served_at')
                                            ->displayFormat('d/m/Y H:i')
                                            ->visible(fn (callable $get) => in_array($get('status'), ['served', 'completed'])),

                                        Forms\Components\DateTimePicker::make('completed_at')
                                            ->displayFormat('d/m/Y H:i')
                                            ->visible(fn (callable $get) => $get('status') === 'completed'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(FilamentTable $table): FilamentTable
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->copyMessage('Order number copied'),

                Tables\Columns\TextColumn::make('restaurant.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('table.table_number')
                    ->label('Table')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'confirmed',
                        'info' => 'preparing',
                        'success' => 'ready',
                        'success' => 'served',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'gray' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'warning' => 'refunded',
                    ])
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('discount.name')
                    ->label('Discount')
                    ->toggleable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('estimated_prep_time')
                    ->suffix(' min')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'preparing' => 'Preparing',
                        'ready' => 'Ready',
                        'served' => 'Served',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print_receipt')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Order $record): string => route('orders.print', $record))
                    ->openUrlInNewTab(),
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
                                    'confirmed' => 'Confirmed',
                                    'preparing' => 'Preparing',
                                    'ready' => 'Ready',
                                    'served' => 'Served',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Order Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('order_number')
                                    ->weight(FontWeight::Bold)
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('restaurant.name'),
                                Infolists\Components\TextEntry::make('table.table_number')
                                    ->label('Table'),
                            ]),
                        
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('customer_name'),
                                Infolists\Components\TextEntry::make('customer_phone'),
                                Infolists\Components\TextEntry::make('customer.email')
                                    ->default('-'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'gray',
                                        'confirmed' => 'warning',
                                        'preparing' => 'info',
                                        'ready' => 'success',
                                        'served' => 'success',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                    }),
                                Infolists\Components\TextEntry::make('payment_status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'gray',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'refunded' => 'warning',
                                    }),
                            ]),

                        Infolists\Components\TextEntry::make('special_instructions')
                            ->columnSpanFull()
                            ->default('-'),
                    ]),

                Infolists\Components\Section::make('Pricing Details')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('tax_amount')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('service_charge')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('discount_amount')
                                    ->money('IDR'),
                            ]),
                        
                        Infolists\Components\TextEntry::make('total_amount')
                            ->money('IDR')
                            ->weight(FontWeight::Bold)
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                        Infolists\Components\TextEntry::make('discount.name')
                            ->label('Applied Discount')
                            ->default('-'),
                    ]),

                Infolists\Components\Section::make('Timeline')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Ordered At')
                                    ->dateTime('d/m/Y H:i'),
                                Infolists\Components\TextEntry::make('confirmed_at')
                                    ->dateTime('d/m/Y H:i')
                                    ->default('-'),
                                Infolists\Components\TextEntry::make('ready_at')
                                    ->dateTime('d/m/Y H:i')
                                    ->default('-'),
                                Infolists\Components\TextEntry::make('served_at')
                                    ->dateTime('d/m/Y H:i')
                                    ->default('-'),
                                Infolists\Components\TextEntry::make('completed_at')
                                    ->dateTime('d/m/Y H:i')
                                    ->default('-'),
                                Infolists\Components\TextEntry::make('estimated_prep_time')
                                    ->suffix(' minutes')
                                    ->default('-'),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    private static function calculateTotal(callable $set, callable $get): void
    {
        $subtotal = floatval($get('subtotal') ?? 0);
        $taxAmount = floatval($get('tax_amount') ?? 0);
        $serviceCharge = floatval($get('service_charge') ?? 0);
        $discountAmount = floatval($get('discount_amount') ?? 0);

        $total = $subtotal + $taxAmount + $serviceCharge - $discountAmount;
        
        $set('total_amount', max(0, $total));
    }
}