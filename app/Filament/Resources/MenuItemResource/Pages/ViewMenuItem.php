<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Route;

class ViewMenuItem extends ViewRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            // Customer Preview Action - dengan pengecekan route
            Actions\Action::make('preview')
                ->label('Customer Preview')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(function (): ?string {
                    // Cek apakah route exists sebelum menggunakannya
                    if (Route::has('menu.preview')) {
                        return route('menu.preview', $this->getRecord());
                    }
                    return null;
                })
                ->visible(fn (): bool => Route::has('menu.preview'))
                ->openUrlInNewTab(),
            
            Actions\ActionGroup::make([
                // Analytics Action
                Actions\Action::make('view_analytics')
                    ->label('View Analytics')
                    ->icon('heroicon-o-chart-bar')
                    ->color('gray')
                    ->url(function (): ?string {
                        if (Route::has('filament.admin.resources.menu-items.analytics')) {
                            return route('filament.admin.resources.menu-items.analytics', $this->getRecord());
                        }
                        return null;
                    })
                    ->visible(fn (): bool => Route::has('filament.admin.resources.menu-items.analytics')),
                
                // Price History Modal
                Actions\Action::make('price_history')
                    ->label('Price History')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->modalContent(function () {
                        // Buat content sederhana jika view tidak ada
                        try {
                            return view('filament.components.price-history', [
                                'menuItem' => $this->getRecord()
                            ]);
                        } catch (\Exception $e) {
                            return view('filament.components.simple-modal', [
                                'title' => 'Price History',
                                'content' => 'Price history feature coming soon...',
                                'menuItem' => $this->getRecord()
                            ]);
                        }
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                
                // QR Code Generation
                Actions\Action::make('export_qr')
                    ->label('Generate QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->action(function () {
                        $menuItem = $this->getRecord();
                        
                        // Coba buat URL yang valid
                        $url = url("/menu/item/{$menuItem->id}"); // Fallback URL
                        
                        // Jika route tersedia, gunakan route yang proper
                        if (Route::has('menu.item')) {
                            try {
                                $url = route('menu.item', [
                                    'restaurant' => $menuItem->restaurant->slug ?? $menuItem->restaurant_id,
                                    'item' => $menuItem->slug ?? $menuItem->id
                                ]);
                            } catch (\Exception $e) {
                                // Fallback jika ada masalah
                                $url = url("/menu/item/{$menuItem->id}");
                            }
                        }
                        
                        // Simple QR code response (atau redirect ke service QR)
                        return redirect()->to("https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url));
                    }),
                    
                // Copy Link Action (sebagai alternatif)
                Actions\Action::make('copy_link')
                    ->label('Copy Link')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->action(function () {
                        $menuItem = $this->getRecord();
                        $url = url("/menu/item/{$menuItem->id}");
                        
                        // JavaScript untuk copy ke clipboard
                        $this->js('navigator.clipboard.writeText("' . $url . '"); $wire.notify("Link copied to clipboard!");');
                    }),
            ])
                ->label('More Actions')
                ->icon('heroicon-o-ellipsis-horizontal')
                ->button(),
        ];
    }
}