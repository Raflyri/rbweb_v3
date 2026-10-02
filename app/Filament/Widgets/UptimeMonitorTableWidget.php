<?php

namespace App\Filament\Widgets;

use App\Models\LaunchpadLink;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UptimeMonitorTableWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LaunchpadLink::query()
                    ->where('is_monitored', true)
                    ->orderByDesc('updated_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(50),

                Tables\Columns\TextColumn::make('monitoring_status')
                    ->label('Uptime Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'up' => 'success',
                        'down' => 'danger',
                        'timeout' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper($state)),

                Tables\Columns\TextColumn::make('http_response_time')
                    ->label('Response Time')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' ms' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_checked_at')
                    ->label('Last Checked')
                    ->since()
                    ->sortable(),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}
