<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthenticationLogResource\Pages;
use App\Models\AuthenticationLog;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AuthenticationLogResource extends Resource
{
    protected static ?string $model = AuthenticationLog::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Auth Monitor';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 12;

    // Badge showing today's login count
    public static function getNavigationBadge(): ?string
    {
        $count = AuthenticationLog::where('event', 'login')
            ->whereDate('logged_at', today())
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'info';
    }

    // ── Authorization: Super Admin only ──────────────────────────────────────

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user?->hasRole('super_admin') ?? false;
    }

    // Read-only: no create/edit/delete
    public static function canCreate(): bool        { return false; }
    public static function canEdit($record): bool   { return false; }
    public static function canDelete($record): bool { return false; }

    // ── Form (unused — read-only resource) ───────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('logged_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y · H:i:s')
                    ->sortable()
                    ->description(fn (AuthenticationLog $record): string =>
                        $record->logged_at->diffForHumans()
                    ),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->description(fn (AuthenticationLog $record): string =>
                        $record->user?->email ?? '—'
                    ),

                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login'  => 'success',
                        'logout' => 'gray',
                        default  => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'login'  => '🔑 Login',
                        'logout' => '🚪 Logout',
                        default  => ucfirst($state),
                    }),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('country')
                    ->label('Location')
                    ->formatStateUsing(function (?string $state, AuthenticationLog $record): string {
                        if (! $state) {
                            return '—';
                        }
                        // Simple country → flag emoji mapping for common countries
                        $flags = [
                            'Indonesia'      => '🇮🇩',
                            'United States'  => '🇺🇸',
                            'Singapore'      => '🇸🇬',
                            'Malaysia'       => '🇲🇾',
                            'Japan'          => '🇯🇵',
                            'Australia'      => '🇦🇺',
                            'United Kingdom' => '🇬🇧',
                            'Germany'        => '🇩🇪',
                            'Netherlands'    => '🇳🇱',
                        ];
                        $flag = $flags[$state] ?? '🌍';
                        $city = $record->city ? ", {$record->city}" : '';
                        return "{$flag} {$state}{$city}";
                    })
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('user_agent')
                    ->label('Browser / OS')
                    ->limit(55)
                    ->tooltip(fn (AuthenticationLog $record): ?string => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
            ])
            ->defaultSort('logged_at', 'desc')
            ->filters([
                // ── Event type filter ────────────────────────────────────────
                SelectFilter::make('event')
                    ->label('Event Type')
                    ->options([
                        'login'  => '🔑 Login',
                        'logout' => '🚪 Logout',
                    ]),

                // ── User filter ──────────────────────────────────────────────
                SelectFilter::make('user_id')
                    ->label('User')
                    ->options(fn (): array =>
                        User::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->searchable(),

                // ── Date range filter ────────────────────────────────────────
                Filter::make('date_range')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('from')
                            ->label('From')
                            ->placeholder('Start date'),
                        DatePicker::make('until')
                            ->label('Until')
                            ->placeholder('End date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'],
                                fn (Builder $q, $date) => $q->whereDate('logged_at', '>=', $date)
                            )
                            ->when($data['until'],
                                fn (Builder $q, $date) => $q->whereDate('logged_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From: ' . $data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Until: ' . $data['until'];
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(3)
            ->recordActions([])     // Read-only — no row actions
            ->toolbarActions([]);   // No bulk actions either
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthenticationLogs::route('/'),
        ];
    }
}
