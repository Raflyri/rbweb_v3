<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\UserChangeRequest;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 10;

    // ── Authorization ─────────────────────────────────────────────────────────

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    // ── Form ──────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')
                ->label('Full Name')
                ->required()
                ->maxLength(255)
                ->autofocus(),

            TextInput::make('email')
                ->label('Email Address')
                ->email()
                ->required()
                ->unique(table: User::class, column: 'email', ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create')
                ->minLength(8)
                ->helperText(fn (string $operation) => $operation === 'edit'
                    ? 'Leave blank to keep the current password.'
                    : null),

            Select::make('roles')
                ->label('Roles')
                ->multiple()
                ->relationship('roles', 'name')
                ->options(function (): array {
                    /** @var \App\Models\User|null $actor */
                    $actor = Auth::user();

                    // Super Admin sees all roles; Admin cannot assign super_admin
                    if ($actor?->hasRole('super_admin')) {
                        return Role::orderBy('name')->pluck('name', 'id')->toArray();
                    }

                    return Role::orderBy('name')
                        ->where('name', '!=', 'super_admin')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->preload()
                ->searchable()
                ->required()
                ->helperText('Admin users cannot assign the Super Admin role.'),

        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        /** @var \App\Models\User|null $actor */
        $actor = Auth::user();
        $isSuperAdmin = $actor?->hasRole('super_admin');

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin'       => 'warning',
                        'premium'     => 'success',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => str($state)->headline()),

                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->state(fn (User $record): bool => $record->email_verified_at !== null),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                // Edit: Super Admin → opens edit form directly
                // Admin → edit form submits as a pending request (handled in EditUser page)
                EditAction::make(),

                // Delete: Super Admin → immediate; Admin → creates pending request
                DeleteAction::make()
                    ->before(function (User $record, DeleteAction $action) use ($actor, $isSuperAdmin) {
                        if ($isSuperAdmin) {
                            // Super Admin: delete directly — carry on
                            return;
                        }

                        // Admin: create a pending delete request instead
                        // Check for an existing pending request for this user
                        $alreadyPending = UserChangeRequest::where('target_user_id', $record->id)
                            ->where('action', 'delete')
                            ->where('status', 'pending')
                            ->exists();

                        if ($alreadyPending) {
                            Notification::make()
                                ->title('Request already pending')
                                ->body('A delete request for this user is already awaiting Super Admin approval.')
                                ->warning()
                                ->send();
                        } else {
                            UserChangeRequest::create([
                                'requested_by'   => $actor->id,
                                'target_user_id' => $record->id,
                                'action'         => 'delete',
                                'payload'        => null,
                                'status'         => 'pending',
                            ]);

                            Notification::make()
                                ->title('Delete request submitted')
                                ->body('Your request has been sent to the Super Admin for approval.')
                                ->success()
                                ->send();
                        }

                        // Cancel the actual deletion for Admin
                        $action->cancel();
                    }),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->visible(function (): bool {
                        /** @var \App\Models\User|null $u */
                        $u = Auth::user();
                        return $u?->hasRole('super_admin') ?? false;
                    }),
            ]);
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
