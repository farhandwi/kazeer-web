<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use App\Models\Discount;
use Filament\Actions;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewDiscount extends ViewRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->icon('heroicon-o-pencil'),
            Actions\Action::make('toggle_status')
                ->label(fn (Discount $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                ->icon(fn (Discount $record): string => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                ->color(fn (Discount $record): string => $record->is_active ? 'warning' : 'success')
                ->action(fn (Discount $record) => $record->update(['is_active' => !$record->is_active]))
                ->requiresConfirmation()
                ->after(fn (Discount $record) => $this->redirect($this->getResource()::getUrl('view', ['record' => $record]))),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Components\Section::make('Basic Information')
                ->schema([
                    Components\Grid::make(2)->schema([
                        Components\TextEntry::make('name')
                            ->weight(FontWeight::Bold)
                            ->size(Components\TextEntry\TextEntrySize::Large),
                        Components\TextEntry::make('code')
                            ->label('Discount Code')
                            ->placeholder('No code set')
                            ->copyable(),
                    ]),
                    Components\TextEntry::make('description')
                        ->placeholder('No description provided')
                        ->columnSpanFull(),
                    Components\Grid::make(3)->schema([
                        Components\TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'percentage' => 'Percentage',
                                'fixed_amount' => 'Fixed Amount',
                                default => ucfirst($state),
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'percentage' => 'success',
                                'fixed_amount' => 'info',
                                default => 'gray',
                            }),
                        Components\TextEntry::make('value')
                            ->label('Discount Value')
                            ->weight(FontWeight::Bold)
                            ->getStateUsing(fn (Discount $record): string => $record->type === 'percentage'
                                ? $record->value . '%'
                                : 'IDR ' . number_format($record->value, 0, ',', '.')
                            ),
                        Components\TextEntry::make('minimum_order')
                            ->money('IDR')
                            ->placeholder('No minimum order'),
                    ]),
                    Components\TextEntry::make('maximum_discount')
                        ->label('Maximum Discount Amount')
                        ->money('IDR')
                        ->placeholder('No maximum limit')
                        ->visible(fn (Discount $record): bool => $record->type === 'percentage'),
                ]),
            
            Components\Section::make('Status & Schedule')
                ->schema([
                    Components\Grid::make(2)->schema([
                        Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'scheduled' => 'warning',
                                'expired' => 'danger',
                                'inactive' => 'gray',
                                'limit_reached' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => 'Active',
                                'scheduled' => 'Scheduled',
                                'expired' => 'Expired',
                                'inactive' => 'Inactive',
                                'limit_reached' => 'Limit Reached',
                                default => ucfirst($state),
                            }),
                        Components\IconEntry::make('is_active')
                            ->label('Enabled')
                            ->boolean(),
                    ]),
                    Components\Grid::make(2)->schema([
                        Components\TextEntry::make('starts_at')
                            ->label('Start Date & Time')
                            ->dateTime('d/m/Y H:i'),
                        Components\TextEntry::make('expires_at')
                            ->label('Expiry Date & Time')
                            ->dateTime('d/m/Y H:i'),
                    ]),
                ]),

            Components\Section::make('Usage & Limits')
                ->schema([
                    Components\Grid::make(3)->schema([
                        Components\TextEntry::make('used_count')
                            ->label('Times Used')
                            ->badge()
                            ->color('info'),
                        Components\TextEntry::make('usage_limit')
                            ->label('Total Usage Limit')
                            ->placeholder('Unlimited'),
                        Components\TextEntry::make('usage_limit_per_customer')
                            ->label('Per Customer Limit')
                            ->placeholder('Unlimited'),
                    ]),
                    Components\TextEntry::make('usage_progress')
                        ->label('Usage Progress')
                        ->badge()
                        ->getStateUsing(function (Discount $record): string {
                            if (!$record->usage_limit) {
                                return $record->used_count . ' times used';
                            }
                            $percentage = round(($record->used_count / $record->usage_limit) * 100, 1);
                            return "{$record->used_count}/{$record->usage_limit} ({$percentage}%)";
                        })
                        ->color(function (Discount $record): string {
                            if (!$record->usage_limit) {
                                return 'gray';
                            }
                            $percentage = ($record->used_count / $record->usage_limit) * 100;
                            return match (true) {
                                $percentage >= 100 => 'danger',
                                $percentage >= 80 => 'warning',
                                default => 'success',
                            };
                        }),
                    Components\TextEntry::make('customer_eligibility')
                        ->label('Customer Eligibility')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'all' => 'All Customers',
                            'new_customers' => 'New Customers Only',
                            'returning_customers' => 'Returning Customers Only',
                            default => ucfirst(str_replace('_', ' ', $state)),
                        }),
                ]),

            Components\Section::make('Applicable Items')
                ->schema([
                    Components\TextEntry::make('applicable_scope')
                        ->label('Discount Scope')
                        ->getStateUsing(function (Discount $record): string {
                            $scopes = [
                                $record->applicable_restaurants ? count($record->applicable_restaurants) . ' specific restaurant(s)' : 'All restaurants',
                                $record->applicable_categories ? count($record->applicable_categories) . ' specific category(ies)' : 'All categories',
                                $record->applicable_menu_items ? count($record->applicable_menu_items) . ' specific menu item(s)' : 'All menu items',
                            ];
                            return implode(' • ', $scopes);
                        })
                        ->badge()
                        ->columnSpanFull(),
                    Components\TextEntry::make('applicable_restaurants_list')
                        ->label('Applicable Restaurants')
                        ->list(Components\TextEntry\TextEntrySize::ExtraSmall)
                        ->badge()
                        ->placeholder('No specific restaurants')
                        ->visible(fn (Discount $record): bool => !empty($record->applicable_restaurants))
                        ->getStateUsing(fn (Discount $record): array => \App\Models\Restaurant::whereIn('id', $record->applicable_restaurants)->pluck('name')->toArray()),
                    Components\TextEntry::make('applicable_categories_list')
                        ->label('Applicable Categories')
                        ->list(Components\TextEntry\TextEntrySize::ExtraSmall)
                        ->badge()
                        ->placeholder('No specific categories')
                        ->visible(fn (Discount $record): bool => !empty($record->applicable_categories))
                        ->getStateUsing(fn (Discount $record): array => \App\Models\Category::whereIn('id', $record->applicable_categories)->pluck('name')->toArray()),
                    Components\TextEntry::make('applicable_menu_items_count')
                        ->label('Applicable Menu Items')
                        ->getStateUsing(fn (Discount $record): ?string => $record->applicable_menu_items ? count($record->applicable_menu_items) . ' menu item(s) selected' : null)
                        ->visible(fn (Discount $record): bool => !empty($record->applicable_menu_items))
                        ->badge()
                        ->color('info'),
                ])
                ->collapsible(),

            Components\Section::make('Timestamps')
                ->schema([
                    Components\Grid::make(2)->schema([
                        Components\TextEntry::make('created_at')
                            ->dateTime('d/m/Y H:i'),
                        Components\TextEntry::make('updated_at')
                            ->dateTime('d/m/Y H:i'),
                    ]),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}