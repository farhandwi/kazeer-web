<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Filament\Resources\MenuItemResource\RelationManagers;
use App\Models\MenuItem;
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

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Menu Items';

    protected static ?string $modelLabel = 'Menu Item';

    protected static ?string $pluralModelLabel = 'Menu Items';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Menu Item Details')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('restaurant_id')
                                            ->relationship('restaurant', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => $set('category_id', null)),

                                        Forms\Components\Select::make('category_id')
                                            ->relationship('category', 'name', 
                                                fn (Builder $query, callable $get) => $query->where('restaurant_id', $get('restaurant_id'))
                                            )
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->disabled(fn (callable $get) => !$get('restaurant_id'))
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('slug')
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('description')
                                                    ->rows(3),
                                                Forms\Components\Toggle::make('is_active')
                                                    ->default(true),
                                            ]),

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

                                        Forms\Components\TextInput::make('price')
                                            ->required()
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                $discountPercentage = $get('discount_percentage');
                                                if ($discountPercentage && $state) {
                                                    $discountedPrice = $state - ($state * $discountPercentage / 100);
                                                    $set('discounted_price', $discountedPrice);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('preparation_time')
                                            ->required()
                                            ->numeric()
                                            ->suffix('minutes')
                                            ->default(15)
                                            ->minValue(1)
                                            ->maxValue(120),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),

                                        // Forms\Components\Select::make('spice_level')
                                        //     ->options([
                                        //         'none' => 'None',
                                        //         'mild' => 'Mild',
                                        //         'medium' => 'Medium',
                                        //         'hot' => 'Hot',
                                        //         'very_hot' => 'Very Hot',
                                        //     ])
                                        //     ->default('none')
                                        //     ->required(),
                                    ]),

                                Forms\Components\Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Toggle::make('is_available')
                                            ->label('Available')
                                            ->default(true),

                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Featured')
                                            ->default(false),

                                        Forms\Components\Toggle::make('has_options')
                                            ->label('Has Customization Options')
                                            ->default(false)
                                            ->live(),
                                    ]),

                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->directory('menu-items')
                                    ->maxSize(2048)
                                    ->imageEditor()
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Discount Settings')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('is_on_discount')
                                            ->label('Enable Discount')
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                if (!$state) {
                                                    $set('discount_percentage', null);
                                                    $set('discounted_price', null);
                                                    $set('discount_starts_at', null);
                                                    $set('discount_ends_at', null);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('discount_percentage')
                                            ->label('Discount Percentage (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->step(0.01)
                                            ->visible(fn (Get $get): bool => $get('is_on_discount'))
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                $price = $get('price');
                                                if ($price && $state) {
                                                    $discountedPrice = $price - ($price * $state / 100);
                                                    $set('discounted_price', $discountedPrice);
                                                }
                                            }),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('discounted_price')
                                            ->label('Discounted Price')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->disabled()
                                            ->dehydrated()
                                            ->visible(fn (Get $get): bool => $get('is_on_discount')),

                                        Forms\Components\Placeholder::make('savings_preview')
                                            ->label('Savings')
                                            ->content(function (Get $get): string {
                                                $price = $get('price');
                                                $discountedPrice = $get('discounted_price');
                                                
                                                if (!$price || !$discountedPrice) {
                                                    return 'No savings calculated';
                                                }
                                                
                                                $savings = $price - $discountedPrice;
                                                $percentage = round(($savings / $price) * 100, 2);
                                                
                                                return 'Save Rp ' . number_format($savings, 0, ',', '.') . 
                                                       ' (' . $percentage . '%)';
                                            })
                                            ->visible(fn (Get $get): bool => $get('is_on_discount') && $get('discounted_price')),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('discount_starts_at')
                                            ->label('Discount Starts At')
                                            ->visible(fn (Get $get): bool => $get('is_on_discount'))
                                            ->default(now())
                                            ->timezone('Asia/Jakarta'),

                                        Forms\Components\DateTimePicker::make('discount_ends_at')
                                            ->label('Discount Ends At')
                                            ->visible(fn (Get $get): bool => $get('is_on_discount'))
                                            ->timezone('Asia/Jakarta')
                                            ->after('discount_starts_at'),
                                    ]),

                                Forms\Components\Placeholder::make('discount_status_info')
                                    ->label('Discount Status')
                                    ->content(function (Get $get): string {
                                        if (!$get('is_on_discount')) {
                                            return 'No discount configured';
                                        }
                                        
                                        $startsAt = $get('discount_starts_at');
                                        $endsAt = $get('discount_ends_at');
                                        $now = now();
                                        
                                        if ($startsAt && $startsAt > $now) {
                                            return '⏳ Scheduled - Starts at ' . $startsAt;
                                        }
                                        
                                        if ($endsAt && $endsAt < $now) {
                                            return '⏰ Expired - Ended at ' . $endsAt;
                                        }
                                        
                                        return '✅ Active';
                                    })
                                    ->visible(fn (Get $get): bool => $get('is_on_discount'))
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Allergens & Additional Info')
                            ->schema([
                                Forms\Components\CheckboxList::make('allergens')
                                    ->options([
                                        'gluten' => 'Gluten',
                                        'dairy' => 'Dairy',
                                        'eggs' => 'Eggs',
                                        'fish' => 'Fish',
                                        'shellfish' => 'Shellfish',
                                        'tree_nuts' => 'Tree Nuts',
                                        'peanuts' => 'Peanuts',
                                        'soy' => 'Soy',
                                        'sesame' => 'Sesame',
                                    ])
                                    ->columns(3)
                                    ->gridDirection('row'),

                                Forms\Components\KeyValue::make('default_options')
                                    ->label('Default Options (JSON)')
                                    ->keyLabel('Option Category')
                                    ->valueLabel('Default Option')
                                    ->visible(fn (callable $get) => $get('has_options'))
                                    ->helperText('Set default options for this menu item'),
                            ]),

                        Tabs\Tab::make('Customization Options')
                            ->schema([
                                Forms\Components\Placeholder::make('options_info')
                                    ->label('Menu Options')
                                    ->content('Configure which option categories are available for this menu item.')
                                    ->visible(fn (callable $get) => $get('has_options')),

                                Forms\Components\Repeater::make('menuItemOptionCategories')
                                    ->relationship('menuItemOptionCategories')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Select::make('menu_option_category_id')
                                                    ->label('Option Category')
                                                    ->relationship('optionCategory', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255),

                                                        Forms\Components\TextInput::make('slug')
                                                            ->maxLength(255),

                                                        Forms\Components\Textarea::make('description')
                                                            ->rows(2),

                                                        Forms\Components\Select::make('type')
                                                            ->options([
                                                                'single'   => 'Single Selection (Radio)',
                                                                'multiple' => 'Multiple Selection (Checkbox)',
                                                            ])
                                                            ->default('single')
                                                            ->required(),

                                                        Forms\Components\Toggle::make('is_required')
                                                            ->default(false),
                                                    ]),

                                                Forms\Components\Toggle::make('is_required')
                                                    ->label('Required')
                                                    ->default(false),

                                                Forms\Components\TextInput::make('sort_order')
                                                    ->label('Order')
                                                    ->numeric()
                                                    ->default(0),
                                            ]),
                                    ])
                                    ->visible(fn (callable $get) => $get('has_options'))
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => 
                                        isset($state['menu_option_category_id'])
                                            ? \App\Models\MenuOptionCategory::find($state['menu_option_category_id'])?->name ?? 'Unknown Category'
                                            : 'New Option Category'
                                    )
                                    ->addActionLabel('Add Option Category')
                                    ->deleteAction(
                                        fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation()
                                    ),
                            ])
                            ->visible(fn (callable $get) => $get('has_options')),
                        ])
                        ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular()
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('restaurant.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Original Price')
                    ->money('IDR')
                    ->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('current_price')
                    ->label('Current Price')
                    ->getStateUsing(fn (MenuItem $record): float => $record->getCurrentPrice())
                    ->money('IDR')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color(fn (MenuItem $record): string => $record->isDiscountActive() ? 'success' : 'gray'),

                Tables\Columns\BadgeColumn::make('discount_status')
                    ->label('Discount')
                    ->getStateUsing(fn (MenuItem $record): string => $record->discount_status)
                    ->colors([
                        'gray' => 'no_discount',
                        'warning' => 'scheduled',
                        'danger' => 'expired',
                        'success' => 'active',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'no_discount' => 'No Discount',
                        'scheduled' => 'Scheduled',
                        'expired' => 'Expired',
                        'active' => 'Active',
                        default => ucfirst($state),
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'no_discount' => 'heroicon-o-minus',
                        'scheduled' => 'heroicon-o-clock',
                        'expired' => 'heroicon-o-x-mark',
                        'active' => 'heroicon-o-check',
                        default => 'heroicon-o-minus',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Discount %')
                    ->suffix('%')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                // Tables\Columns\BadgeColumn::make('spice_level')
                //     ->colors([
                //         'gray' => 'none',
                //         'success' => 'mild',
                //         'warning' => 'medium',
                //         'danger' => 'hot',
                //         'danger' => 'very_hot',
                //     ])
                //     ->formatStateUsing(fn (string $state): string => match ($state) {
                //         'none' => 'None',
                //         'mild' => 'Mild',
                //         'medium' => 'Medium',
                //         'hot' => 'Hot',
                //         'very_hot' => 'Very Hot',
                //     })
                //     ->sortable()
                //     ->toggleable(),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('has_options')
                    ->label('Has Options')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('preparation_time')
                    ->suffix(' min')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                // Tables\Filters\SelectFilter::make('spice_level')
                //     ->options([
                //         'none' => 'None',
                //         'mild' => 'Mild',
                //         'medium' => 'Medium',
                //         'hot' => 'Hot',
                //         'very_hot' => 'Very Hot',
                //     ])
                //     ->multiple(),

                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueLabel('Available only')
                    ->falseLabel('Unavailable only')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueLabel('Featured only')
                    ->falseLabel('Non-featured only')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('has_options')
                    ->label('Has Options')
                    ->boolean()
                    ->trueLabel('With options')
                    ->falseLabel('Without options')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_on_discount')
                    ->label('Discount Status')
                    ->boolean()
                    ->trueLabel('With discount')
                    ->falseLabel('Without discount')
                    ->native(false),

                Tables\Filters\Filter::make('discount_active')
                    ->label('Active Discounts Only')
                    ->query(fn (Builder $query): Builder => $query->onDiscount()),

                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('price_from')
                                    ->label('Price from')
                                    ->numeric()
                                    ->prefix('Rp'),
                                Forms\Components\TextInput::make('price_to')
                                    ->label('Price to')
                                    ->numeric()
                                    ->prefix('Rp'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_availability')
                    ->label(fn (MenuItem $record) => $record->is_available ? 'Mark Unavailable' : 'Mark Available')
                    ->icon(fn (MenuItem $record) => $record->is_available ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (MenuItem $record) => $record->is_available ? 'warning' : 'success')
                    ->action(fn (MenuItem $record) => $record->update(['is_available' => !$record->is_available]))
                    ->requiresConfirmation()
                    ->modalHeading(fn (MenuItem $record) => $record->is_available ? 'Mark as Unavailable' : 'Mark as Available')
                    ->modalDescription(fn (MenuItem $record) => 
                        $record->is_available 
                            ? 'This will hide the item from customer menu.' 
                            : 'This will show the item in customer menu.'
                    ),
                Tables\Actions\Action::make('apply_discount')
                    ->label('Apply Discount')
                    ->icon('heroicon-o-tag')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('Discount Percentage (%)')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Ends At')
                            ->after('starts_at'),
                    ])
                    ->action(function (MenuItem $record, array $data) {
                        $record->applyDiscount(
                            $data['discount_percentage'],
                            $data['starts_at'] ?? now(),
                            $data['ends_at'] ?? null
                        );
                    })
                    ->visible(fn (MenuItem $record) => !$record->is_on_discount),
                Tables\Actions\Action::make('remove_discount')
                    ->label('Remove Discount')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn (MenuItem $record) => $record->removeDiscount())
                    ->requiresConfirmation()
                    ->visible(fn (MenuItem $record) => $record->is_on_discount),
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (MenuItem $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $record->name . ' (Copy)';
                        $newRecord->slug = $record->slug . '-copy';
                        $newRecord->is_featured = false;
                        $newRecord->save();
                        
                        // Copy option categories relationships
                        foreach ($record->menuOptionCategories as $optionCategory) {
                            $newRecord->menuOptionCategories()->attach($optionCategory->id, [
                                'is_required' => $optionCategory->pivot->is_required,
                                'sort_order' => $optionCategory->pivot->sort_order,
                            ]);
                        }
                        
                        return redirect()->route('filament.admin.resources.menu-items.edit', $newRecord);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Menu Item')
                    ->modalDescription('This will create a copy of this menu item with all its options.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggle_availability')
                        ->label('Toggle Availability')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['is_available' => $data['is_available']]);
                            });
                        })
                        ->form([
                            Forms\Components\Select::make('is_available')
                                ->label('Availability')
                                ->options([
                                    true => 'Available',
                                    false => 'Unavailable',
                                ])
                                ->required(),
                        ])
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('apply_bulk_discount')
                        ->label('Apply Discount')
                        ->icon('heroicon-o-tag')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('discount_percentage')
                                ->label('Discount Percentage (%)')
                                ->required()
                                ->numeric()
                                ->suffix('%')
                                ->minValue(0)
                                ->maxValue(100),
                            Forms\Components\DateTimePicker::make('starts_at')
                                ->label('Starts At')
                                ->default(now()),
                            Forms\Components\DateTimePicker::make('ends_at')
                                ->label('Ends At')
                                ->after('starts_at'),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->applyDiscount(
                                    $data['discount_percentage'],
                                    $data['starts_at'] ?? now(),
                                    $data['ends_at'] ?? null
                                );
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('remove_bulk_discount')
                        ->label('Remove Discount')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->removeDiscount();
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('update_category')
                        ->label('Change Category')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Forms\Components\Select::make('category_id')
                                ->relationship('category', 'name')
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['category_id' => $data['category_id']]);
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
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->weight(FontWeight::Bold)
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                Infolists\Components\TextEntry::make('restaurant.name')
                                    ->label('Restaurant'),
                                Infolists\Components\TextEntry::make('category.name')
                                    ->label('Category'),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->placeholder('No description provided'),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('price')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->label('Original Price'),
                                Infolists\Components\TextEntry::make('preparation_time')
                                    ->suffix(' minutes'),
                                // Infolists\Components\TextEntry::make('spice_level')
                                //     ->badge()
                                //     ->color(fn (string $state): string => match ($state) {
                                //         'none' => 'gray',
                                //         'mild' => 'success',
                                //         'medium' => 'warning',
                                //         'hot' => 'danger',
                                //         'very_hot' => 'danger',
                                //         default => 'gray',
                                //     })
                                //     ->formatStateUsing(fn (string $state): string => match ($state) {
                                //         'none' => 'None',
                                //         'mild' => 'Mild',
                                //         'medium' => 'Medium',
                                //         'hot' => 'Hot',
                                //         'very_hot' => 'Very Hot',
                                //         default => ucfirst($state),
                                //     }),
                                Infolists\Components\TextEntry::make('sort_order'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\IconEntry::make('is_available')
                                    ->label('Available')
                                    ->boolean(),
                                Infolists\Components\IconEntry::make('is_featured')
                                    ->label('Featured')
                                    ->boolean(),
                                Infolists\Components\IconEntry::make('has_options')
                                    ->label('Has Options')
                                    ->boolean(),
                            ]),

                        Infolists\Components\ImageEntry::make('image')
                            ->height(200)
                            ->columnSpanFull()
                            ->placeholder('No image uploaded'),
                    ]),

                // DISCOUNT SECTION
                Infolists\Components\Section::make('Discount Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('formatted_current_price')
                                    ->label('Current Price')
                                    ->badge()
                                    ->color(fn (MenuItem $record): string => $record->isDiscountActive() ? 'success' : 'gray'),

                                Infolists\Components\TextEntry::make('discount_percentage')
                                    ->label('Discount')
                                    ->suffix('%')
                                    ->placeholder('No discount')
                                    ->badge()
                                    ->color('info'),

                                Infolists\Components\TextEntry::make('discount_status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'no_discount' => 'gray',
                                        'scheduled' => 'warning',
                                        'expired' => 'danger',
                                        'active' => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'no_discount' => 'No Discount',
                                        'scheduled' => 'Scheduled',
                                        'expired' => 'Expired',
                                        'active' => 'Active',
                                        default => ucfirst($state),
                                    }),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('discount_starts_at')
                                    ->label('Discount Starts')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Not set')
                                    ->visible(fn (MenuItem $record): bool => $record->is_on_discount),

                                Infolists\Components\TextEntry::make('discount_ends_at')
                                    ->label('Discount Ends')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('No end date')
                                    ->visible(fn (MenuItem $record): bool => $record->is_on_discount),
                            ]),

                        Infolists\Components\TextEntry::make('savings_info')
                            ->label('Customer Savings')
                            ->getStateUsing(function (MenuItem $record): string {
                                if (!$record->isDiscountActive()) {
                                    return 'No active discount';
                                }
                                
                                $savings = $record->getSavingsAmount();
                                $percentage = $record->getSavingsPercentage();
                                
                                return 'Saves Rp ' . number_format($savings, 0, ',', '.') . 
                                       ' (' . $percentage . '% off)';
                            })
                            ->badge()
                            ->color('success')
                            ->visible(fn (MenuItem $record): bool => $record->isDiscountActive())
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (MenuItem $record): bool => $record->is_on_discount)
                    ->collapsible(),

                // ALLERGENS SECTION - AMAN
                Infolists\Components\Section::make('Allergens')
                    ->schema([
                        Infolists\Components\TextEntry::make('allergens_list')
                            ->label('Allergens')
                            ->badge()
                            ->getStateUsing(function (MenuItem $record) {
                                if (!$record->allergens || empty($record->allergens)) {
                                    return ['No allergens specified'];
                                }
                                
                                $allergenLabels = [
                                    'gluten' => 'Gluten',
                                    'dairy' => 'Dairy',
                                    'eggs' => 'Eggs',
                                    'fish' => 'Fish',
                                    'shellfish' => 'Shellfish',
                                    'tree_nuts' => 'Tree Nuts',
                                    'peanuts' => 'Peanuts',
                                    'soy' => 'Soy',
                                    'sesame' => 'Sesame',
                                ];
                                
                                return collect($record->allergens)
                                    ->map(fn($allergen) => $allergenLabels[$allergen] ?? ucfirst($allergen))
                                    ->toArray();
                            }),
                    ]),

                // OPTIONS SECTION - AMAN
                Infolists\Components\Section::make('Menu Options')
                    ->schema([
                        // Default Options sebagai string
                        Infolists\Components\TextEntry::make('default_options_display')
                            ->label('Default Options')
                            ->getStateUsing(function (MenuItem $record) {
                                if (!$record->default_options || empty($record->default_options)) {
                                    return 'No default options set';
                                }
                                
                                if (is_array($record->default_options)) {
                                    return collect($record->default_options)
                                        ->map(fn($v, $k) => "$k: $v")
                                        ->implode(', ');
                                }
                                
                                return (string) $record->default_options;
                            }),

                        // Option Categories yang tersedia
                        Infolists\Components\RepeatableEntry::make('optionCategories')
                            ->label('Available Option Categories')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('type')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'single' => 'info',
                                                'multiple' => 'success',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'single' => 'Single Selection',
                                                'multiple' => 'Multiple Selection',
                                                default => ucfirst($state),
                                            }),
                                        Infolists\Components\IconEntry::make('pivot.is_required')
                                            ->label('Required')
                                            ->boolean(),
                                    ]),
                                Infolists\Components\TextEntry::make('description')
                                    ->placeholder('No description')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->visible(fn (MenuItem $record): bool => 
                                $record->has_options && 
                                $record->optionCategories && 
                                $record->optionCategories->isNotEmpty()
                            ),
                    ])
                    ->visible(fn (MenuItem $record): bool => $record->has_options)
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
            RelationManagers\MenuOptionCategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'view' => Pages\ViewMenuItem::route('/{record}'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
            'import' => Pages\ImportMenuItems::route('/import'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['restaurant', 'category']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'restaurant.name', 'category.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [
            'Restaurant' => $record->restaurant?->name,
            'Category' => $record->category?->name,
            'Price' => 'Rp ' . number_format($record->price, 0, ',', '.'),
        ];

        // Add discount info if active
        if ($record->isDiscountActive()) {
            $details['Current Price'] = 'Rp ' . number_format($record->getCurrentPrice(), 0, ',', '.');
            $details['Discount'] = $record->discount_percentage . '% off';
        }

        return $details;
    }
}