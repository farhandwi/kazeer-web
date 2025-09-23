<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TableResource\Pages;
use App\Models\Table;
use App\Models\Restaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class TableResource extends Resource
{
    protected static ?string $model = Table::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationLabel = 'Tables';

    protected static ?string $modelLabel = 'Table';

    protected static ?string $pluralModelLabel = 'Tables';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Table Information')
                    ->schema([
                        Forms\Components\Select::make('restaurant_id')
                            ->label('Restaurant')
                            ->options(Restaurant::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('table_number')
                            ->label('Table Number')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(999),

                        Forms\Components\TextInput::make('capacity')
                            ->label('Capacity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->suffix('people'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Available',
                                'occupied' => 'Occupied',
                                'reserved' => 'Reserved',
                                'maintenance' => 'Maintenance',
                                'inactive' => 'Inactive',
                            ])
                            ->default('available')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(FilamentTable $table): FilamentTable
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('table_number')
                    ->label('Table Number')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacity')
                    ->sortable()
                    ->suffix(' people'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'warning', 
                        'reserved' => 'info',
                        'maintenance' => 'danger',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('table_code')
                    ->label('Table Code')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Table code copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\ImageColumn::make('qr_code_url')
                    ->label('QR Code')
                    ->size(60)
                    ->defaultImageUrl(url('/images/no-qr-code.png'))
                    ->circular(false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('restaurant')
                    ->relationship('restaurant', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'reserved' => 'Reserved',
                        'maintenance' => 'Maintenance',
                        'inactive' => 'Inactive',
                    ]),

                Tables\Filters\Filter::make('capacity')
                    ->form([
                        Forms\Components\TextInput::make('capacity_from')
                            ->label('Capacity From')
                            ->numeric(),
                        Forms\Components\TextInput::make('capacity_to')
                            ->label('Capacity To')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['capacity_from'],
                                fn (Builder $query, $capacity): Builder => $query->where('capacity', '>=', $capacity),
                            )
                            ->when(
                                $data['capacity_to'],
                                fn (Builder $query, $capacity): Builder => $query->where('capacity', '<=', $capacity),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Action::make('regenerate_qr')
                    ->label('Regenerate QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->action(function (Table $record) {
                        try {
                            $record->regenerateQRCode();
                            
                            Notification::make()
                                ->title('QR Code Regenerated')
                                ->body("QR code for Table #{$record->table_number} has been regenerated successfully.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to regenerate QR code: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate QR Code')
                    ->modalDescription('Are you sure you want to regenerate the QR code for this table?')
                    ->modalSubmitActionLabel('Yes, regenerate'),

                Action::make('download_qr')
                    ->label('Download QR')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Table $record) {
                        if (!$record->qr_code_path) {
                            Notification::make()
                                ->title('No QR Code')
                                ->body('Please generate QR code first.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $filePath = storage_path('app/public/' . $record->qr_code_path);
                        if (file_exists($filePath)) {
                            return response()->download($filePath, "table_{$record->table_number}_qr_code.png");
                        }

                        Notification::make()
                            ->title('File Not Found')
                            ->body('QR code file not found.')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (Table $record) => $record->qr_code_path),

                Action::make('copy_order_url')
                    ->label('Copy URL')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->action(function (Table $record) {
                        // Simple notification without JavaScript dispatch
                        Notification::make()
                            ->title('Order URL')
                            ->body('URL: ' . $record->getOrderUrl())
                            ->info()
                            ->duration(5000)
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulk_regenerate_qr')
                        ->label('Regenerate QR Codes')
                        ->icon('heroicon-o-qr-code')
                        ->color('info')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                try {
                                    $record->regenerateQRCode();
                                    $count++;
                                } catch (\Exception $e) {
                                    // Continue with other records
                                }
                            }

                            Notification::make()
                                ->title('QR Codes Regenerated')
                                ->body("Successfully regenerated {$count} QR codes.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulk_update_status')
                        ->label('Update Status')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'available' => 'Available',
                                    'occupied' => 'Occupied',
                                    'reserved' => 'Reserved',
                                    'maintenance' => 'Maintenance',
                                    'inactive' => 'Inactive',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }

                            Notification::make()
                                ->title('Status Updated')
                                ->body('Selected tables status have been updated.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTables::route('/'),
            'create' => Pages\CreateTable::route('/create'),
            'view' => Pages\ViewTable::route('/{record}'),
            'edit' => Pages\EditTable::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['restaurant']);
    }
}