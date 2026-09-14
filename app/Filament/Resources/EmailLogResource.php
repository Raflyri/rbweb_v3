<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailLogResource\Pages;
use App\Models\EmailLog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class EmailLogResource extends Resource
{
    protected static ?string $model = EmailLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';
    protected static string|UnitEnum|null $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Email Logs';
    protected static ?string $modelLabel = 'Log Email';
    protected static ?string $pluralModelLabel = 'Email Logs';
    protected static ?int $navigationSort = 15;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->description(fn (EmailLog $record): string => $record->created_at?->diffForHumans() ?? '—'),

                TextColumn::make('to_email')
                    ->label('Penerima')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent'    => 'success',
                        'failed'  => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sent'    => '✓ Terkirim',
                        'failed'  => '✗ Gagal',
                        default   => ucfirst($state),
                    }),

                TextColumn::make('error_message')
                    ->label('Pesan Error')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'sent'   => 'Terkirim (Sent)',
                        'failed' => 'Gagal (Failed)',
                    ]),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (EmailLog $record): string => 'Log Email: ' . ($record->subject ?: 'Tanpa Subjek'))
                    ->modalDescription(fn (EmailLog $record): string => 'Dicatat pada ' . ($record->created_at?->format('d M Y H:i:s') ?? '—'))
                    ->schema([
                        Placeholder::make('summary')
                            ->label('Informasi Pengiriman')
                            ->content(fn (EmailLog $record) => new HtmlString(
                                '<div style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); font-size: 0.875rem;">' .
                                '<div style="margin-bottom: 0.35rem;"><strong>Penerima:</strong> <span style="font-family: monospace;">' . e($record->to_email) . '</span></div>' .
                                '<div style="margin-bottom: 0.35rem;"><strong>Subjek:</strong> ' . e($record->subject ?? '-') . '</div>' .
                                '<div style="margin-bottom: 0.35rem;"><strong>Status:</strong> ' . ($record->status === 'sent' ? '<span style="color: #22c55e; font-weight: bold;">Terkirim (Success)</span>' : '<span style="color: #ef4444; font-weight: bold;">Gagal (Failed)</span>') . '</div>' .
                                ($record->error_message ? '<div style="margin-top: 0.5rem; padding: 0.5rem; border-radius: 0.375rem; background: rgba(239, 68, 68, 0.1); color: #f87171;"><strong>Error:</strong><br><pre style="white-space: pre-wrap; font-size: 0.8rem; margin-top: 0.25rem;">' . e($record->error_message) . '</pre></div>' : '') .
                                '</div>'
                            )),
                    ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEmailLogs::route('/'),
        ];
    }
}
