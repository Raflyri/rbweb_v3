<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserChangeRequestResource\Pages;
use App\Models\User;
use App\Models\UserChangeRequest;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserChangeRequestResource extends Resource
{
    protected static ?string $model = UserChangeRequest::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Pending Changes';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 11;

    // ── Badge on nav to show pending count ───────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = UserChangeRequest::pending()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    // ── Authorization: Super Admin only ──────────────────────────────────────

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user?->hasRole('super_admin') ?? false;
    }

    // ── Form (read-only view) ─────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delete' => 'danger',
                        'update' => 'warning',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                TextColumn::make('targetUser.name')
                    ->label('Target User')
                    ->searchable()
                    ->description(fn (UserChangeRequest $record): string => $record->targetUser?->email ?? ''),

                TextColumn::make('requester.name')
                    ->label('Requested By')
                    ->searchable()
                    ->description(fn (UserChangeRequest $record): string => $record->requester?->email ?? ''),

                TextColumn::make('payload')
                    ->label('Proposed Changes')
                    ->formatStateUsing(function (?array $state, UserChangeRequest $record): string {
                        if (empty($state) || $record->action !== 'update') {
                            return '—';
                        }
                        $lines = [];
                        foreach ($state as $key => $value) {
                            if ($key === 'password') {
                                $lines[] = "password: [changed]";
                            } elseif ($key === 'roles') {
                                $roleNames = Role::whereIn('id', (array) $value)->pluck('name')->join(', ');
                                $lines[] = "roles: {$roleNames}";
                            } else {
                                $lines[] = "{$key}: {$value}";
                            }
                        }
                        return implode("\n", $lines);
                    })
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('reviewer.name')
                    ->label('Reviewed By')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('reviewed_at')
                    ->label('Reviewed At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('action')
                    ->options([
                        'update' => 'Update',
                        'delete' => 'Delete',
                    ]),
            ])
            ->recordActions([
                // ── APPROVE ──────────────────────────────────────────────
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve this change?')
                    ->modalDescription('This will apply the change immediately to the user account.')
                    ->visible(fn (UserChangeRequest $record): bool => $record->isPending())
                    ->action(function (UserChangeRequest $record): void {
                        /** @var User $target */
                        $target = $record->targetUser;

                        if (! $target) {
                            Notification::make()->title('User not found')->danger()->send();
                            return;
                        }

                        if ($record->action === 'delete') {
                            $target->delete();
                        } elseif ($record->action === 'update') {
                            $updateData = [];

                            if (isset($record->payload['name'])) {
                                $updateData['name'] = $record->payload['name'];
                            }
                            if (isset($record->payload['email'])) {
                                $updateData['email'] = $record->payload['email'];
                            }
                            if (isset($record->payload['password'])) {
                                // Already hashed by the form
                                $updateData['password'] = $record->payload['password'];
                            }

                            if (! empty($updateData)) {
                                $target->update($updateData);
                            }

                            if (isset($record->payload['roles'])) {
                                $roleIds   = (array) $record->payload['roles'];
                                $roleNames = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
                                $target->syncRoles($roleNames);
                            }
                        }

                        $record->update([
                            'status'      => 'approved',
                            'reviewed_by' => auth()->user()?->id,
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Change approved & applied')
                            ->body("The {$record->action} request for {$target?->name} has been applied.")
                            ->success()
                            ->send();
                    }),

                // ── REJECT ───────────────────────────────────────────────
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject this change request?')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('review_note')
                            ->label('Reason for rejection (optional)')
                            ->rows(3),
                    ])
                    ->visible(fn (UserChangeRequest $record): bool => $record->isPending())
                    ->action(function (UserChangeRequest $record, array $data): void {
                        $record->update([
                            'status'      => 'rejected',
                            'reviewed_by' => auth()->user()?->id,
                            'reviewed_at' => now(),
                            'review_note' => $data['review_note'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Request rejected')
                            ->body('The change request has been rejected.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserChangeRequests::route('/'),
        ];
    }
}
