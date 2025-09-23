<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Builder;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Discounts';
    protected static ?string $modelLabel = 'Discount';
    protected static ?string $pluralModelLabel = 'Discounts';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Menu Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Discount Configuration')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('code')
                                            ->label('Discount Code')
                                            ->maxLength(50)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Optional coupon code for customers to enter')
                                            ->columnSpan(1),
                                    ]),

                                Forms\Components\Textarea::make('description')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->options([
                                                'percentage' => 'Percentage (%)',
                                                'fixed_amount' => 'Fixed Amount (Rp)',
                                            ])
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => $set('maximum_discount', null)),

                                        Forms\Components\TextInput::make('value')
                                            ->label(fn (callable $get) => $get('type') === 'percentage' ? 'Percentage (%)' : 'Amount (Rp)')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(fn (callable $get) => $get('type') === 'percentage' ? 100 : null)
                                            ->prefix(fn (callable $get) => $get('type') === 'fixed_amount' ? 'Rp' : null)
                                            ->suffix(fn (callable $get) => $get('type') === 'percentage' ? '%' : null),

                                        Forms\Components\TextInput::make('maximum_discount')
                                            ->label('Max Discount (Rp)')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->minValue(0)
                                            ->visible(fn (callable $get) => $get('type') === 'percentage')
                                            ->helperText('Maximum discount amount for percentage discounts'),
                                    ]),

                                Forms\Components\TextInput::make('minimum_order')
                                    ->label('Minimum Order Amount (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->helperText('Minimum order amount required to use this discount'),
                            ]),

                        Tabs\Tab::make('Schedule & Limits')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('starts_at')
                                            ->label('Start Date & Time')
                                            ->required()
                                            ->default(now())
                                            ->minDate(now()->subDay()),

                                        Forms\Components\DateTimePicker::make('expires_at')
                                            ->label('Expiry Date & Time')
                                            ->required()
                                            ->after('starts_at')
                                            ->minDate(now()),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('usage_limit')
                                            ->label('Total Usage Limit')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Total number of times this discount can be used'),

                                        Forms\Components\TextInput::make('usage_limit_per_customer')
                                            ->label('Usage Limit Per Customer')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('How many times one customer can use this discount'),
                                    ]),

                                Forms\Components\Select::make('customer_eligibility')
                                    ->label('Customer Eligibility')
                                    ->options([
                                        'all' => 'All Customers',
                                        'new_customers' => 'New Customers Only',
                                        'returning_customers' => 'Returning Customers Only',
                                    ])
                                    ->default('all')
                                    ->required(),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Discount must be active to be used'),
                            ]),

                        Tabs\Tab::make('Applicable Items')
                            ->schema([
                                Forms\Components\Section::make('Target Selection')
                                    ->description('Leave empty to apply to all items, or select specific items')
                                    ->schema([
                                        Forms\Components\Select::make('applicable_restaurants')
                                            ->label('Applicable Restaurants')
                                            ->multiple()
                                            ->options(Restaurant::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Leave empty to apply to all restaurants'),

                                        Forms\Components\Select::make('applicable_categories')
                                            ->label('Applicable Categories')
                                            ->multiple()
                                            ->options(Category::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Leave empty to apply to all categories'),

                                        Forms\Components\Select::make('applicable_menu_items')
                                            ->label('Applicable Menu Items')
                                            ->multiple()
                                            ->options(MenuItem::with('restaurant')->get()->mapWithKeys(fn ($item) => [$item->id => $item->restaurant->name . ' - ' . $item->name]))
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Leave empty to apply to all menu items'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->placeholder('No code'),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'percentage',
                        'info' => 'fixed_amount',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Percentage',
                        'fixed_amount' => 'Fixed Amount',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('formatted_value')
                    ->label('Value')
                    ->getStateUsing(fn (Discount $record): string => $record->formatted_value)
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('minimum_order')
                    ->label('Min Order')
                    ->money('IDR')
                    ->placeholder('No minimum'),

                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(fn (Discount $record): string => $record->status)
                    ->colors([
                        'success' => 'active',
                        'warning' => 'scheduled',
                        'danger' => 'expired',
                        'gray' => ['inactive', 'limit_reached'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Active',
                        'scheduled' => 'Scheduled',
                        'expired' => 'Expired',
                        'inactive' => 'Inactive',
                        'limit_reached' => 'Limit Reached',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('usage_progress')
                    ->label('Usage')
                    ->getStateUsing(function (Discount $record): string {
                        if ($record->usage_limit) {
                            return "{$record->used_count}/{$record->usage_limit}";
                        }
                        return $record->used_count . ' times';
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Start')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed_amount' => 'Fixed Amount',
                    ]),

                Tables\Filters\SelectFilter::make('customer_eligibility')
                    ->label('Customer Type')
                    ->options([
                        'all' => 'All Customers',
                        'new_customers' => 'New Customers',
                        'returning_customers' => 'Returning Customers',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('current_discounts')
                    ->label('Currently Valid')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('is_active', true)
                              ->where('starts_at', '<=', now())
                              ->where('expires_at', '>=', now())
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('has_usage_limit')
                    ->label('Has Usage Limit')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('usage_limit'))
                    ->toggle(),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (Discount $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Discount $record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (Discount $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Discount $record) => $record->update(['is_active' => !$record->is_active]))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view' => Pages\ViewDiscount::route('/{record}'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)
                                  ->where('starts_at', '<=', now())
                                  ->where('expires_at', '>=', now())
                                  ->count();
    }
}