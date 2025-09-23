<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Coupons';
    protected static ?string $modelLabel = 'Coupon';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Coupon Information')
                    ->schema([
                        Forms\Components\Select::make('restaurant_id')
                            ->relationship('restaurant', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->extraInputAttributes(['style' => 'text-transform: uppercase;']),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Discount Configuration')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed_amount' => 'Fixed Amount',
                                'free_item' => 'Free Item',
                            ])
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix(fn (Forms\Get $get) => $get('type') === 'percentage' ? '%' : 'IDR'),
                        Forms\Components\TextInput::make('minimum_order_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->minValue(0),
                    ])->columns(3),

                Forms\Components\Section::make('Usage & Validity')
                    ->schema([
                        Forms\Components\TextInput::make('usage_limit')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Unlimited'),
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->required()
                            ->after('valid_from'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'info' => 'percentage',
                        'success' => 'fixed_amount',
                        'warning' => 'free_item',
                    ]),
                Tables\Columns\TextColumn::make('value')
                    ->getStateUsing(function (Coupon $record) {
                        if ($record->type === 'percentage') {
                            return $record->value . '%';
                        } elseif ($record->type === 'fixed_amount') {
                            return 'Rp ' . number_format($record->value);
                        }
                        return $record->value;
                    }),
                Tables\Columns\TextColumn::make('usage_progress')
                    ->getStateUsing(function (Coupon $record) {
                        if ($record->usage_limit) {
                            return $record->used_count . '/' . $record->usage_limit;
                        }
                        return $record->used_count . '/∞';
                    })
                    ->label('Used'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('valid_from')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->dateTime()
                    ->sortable()
                    ->color(function (Coupon $record) {
                        return $record->valid_until->isPast() ? 'danger' : null;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->label('Restaurant'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed_amount' => 'Fixed Amount',
                        'free_item' => 'Free Item',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn ($query) => $query->where('valid_until', '<', now()))
                    ->label('Expired'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}