<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Models\Staff;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Staff';
    protected static ?string $modelLabel = 'Staff';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Staff Information')
                    ->schema([
                        Forms\Components\Select::make('restaurant_id')
                            ->relationship('restaurant', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        // Cek apakah email sudah ada di tabel users (kecuali saat edit)
                                        $existingUser = User::where('email', $value)->first();
                                        if ($existingUser && request()->route('record') === null) {
                                            $fail('Email ini sudah terdaftar sebagai user.');
                                        }
                                    };
                                },
                            ]),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Role & Access')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'manager' => 'Manager',
                                'cashier' => 'Cashier',
                                'kitchen' => 'Kitchen Staff',
                                'waiter' => 'Waiter',
                            ])
                            ->required()
                            ->default('waiter'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Password')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->revealable(),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->same('password')
                            ->dehydrated(false)
                            ->revealable(),
                    ])->columns(2),

                Forms\Components\Section::make('User Account')
                    ->schema([
                        Forms\Components\Placeholder::make('user_info')
                            ->label('User Account')
                            ->content('Staff akan otomatis dibuatkan akun user untuk login ke sistem.')
                            ->columnSpanFull(),
                    ])
                    ->visibleOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'manager',
                        'info' => 'cashier',
                        'success' => 'kitchen',
                        'gray' => 'waiter',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'admin',
                        'heroicon-o-briefcase' => 'manager',
                        'heroicon-o-calculator' => 'cashier',
                        'heroicon-o-fire' => 'kitchen',
                        'heroicon-o-user' => 'waiter',
                    ]),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('user_exists')
                    ->label('User Account')
                    ->boolean()
                    ->getStateUsing(fn ($record) => User::where('email', $record->email)->exists())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->label('Restaurant'),
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'manager' => 'Manager',
                        'cashier' => 'Cashier',
                        'kitchen' => 'Kitchen Staff',
                        'waiter' => 'Waiter',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                Tables\Filters\TernaryFilter::make('has_user')
                    ->label('Has User Account')
                    ->queries(
                        true: fn ($query) => $query->whereExists(function ($subquery) {
                            $subquery->select(DB::raw(1))
                                ->from('users')
                                ->whereColumn('users.email', 'staff.email');
                        }),
                        false: fn ($query) => $query->whereNotExists(function ($subquery) {
                            $subquery->select(DB::raw(1))
                                ->from('users')
                                ->whereColumn('users.email', 'staff.email');
                        }),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reset_password')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->revealable(),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required()
                            ->same('password')
                            ->revealable(),
                    ])
                    ->action(function (Staff $record, array $data) {
                        $hashedPassword = Hash::make($data['password']);
                        
                        // Update password di staff
                        $record->update(['password' => $hashedPassword]);
                        
                        // Update password di users juga
                        User::where('email', $record->email)->update(['password' => $hashedPassword]);
                        
                        Notification::make()
                            ->title('Password berhasil direset')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('create_user')
                    ->label('Create User Account')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn ($record) => !User::where('email', $record->email)->exists())
                    ->action(function (Staff $record) {
                        User::create([
                            'name' => $record->name,
                            'email' => $record->email,
                            'password' => $record->password,
                        ]);
                        
                        Notification::make()
                            ->title('User account berhasil dibuat')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make()
                    ->after(function (Staff $record) {
                        // Hapus user account juga setelah staff dihapus
                        User::where('email', $record->email)->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function ($records) {
                            // Hapus user accounts untuk semua staff yang dihapus
                            $emails = $records->pluck('email')->toArray();
                            User::whereIn('email', $emails)->delete();
                        }),
                    Tables\Actions\BulkAction::make('create_users')
                        ->label('Create User Accounts')
                        ->icon('heroicon-o-user-plus')
                        ->action(function ($records) {
                            $created = 0;
                            foreach ($records as $record) {
                                if (!User::where('email', $record->email)->exists()) {
                                    User::create([
                                        'name' => $record->name,
                                        'email' => $record->email,
                                        'password' => $record->password,
                                    ]);
                                    $created++;
                                }
                            }
                            
                            Notification::make()
                                ->title("$created user accounts berhasil dibuat")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}