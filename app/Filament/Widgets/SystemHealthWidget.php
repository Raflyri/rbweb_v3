<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemHealthWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        // ── DB connection check ───────────────────────────────────────────
        try {
            DB::select('SELECT 1');
            $dbStatus  = 'Connected';
            $dbColor   = 'success';
        } catch (\Exception) {
            $dbStatus  = 'Error';
            $dbColor   = 'danger';
        }

        // ── Queue jobs waiting ────────────────────────────────────────────
        $queueCount = Cache::remember('sys.queue_count', 30, function () {
            try { return DB::table('jobs')->count(); } catch (\Exception) { return '?'; }
        });

        // ── Storage free ──────────────────────────────────────────────────
        $freeBytes  = @disk_free_space(storage_path()) ?: 0;
        $freeMB     = $freeBytes > 0 ? round($freeBytes / 1024 / 1024) : 0;

        // ── Activity last 24h ─────────────────────────────────────────────
        $activityCount = Cache::remember('sys.activity_24h', 60, function () {
            try {
                return \Spatie\Activitylog\Models\Activity::where('created_at', '>=', now()->subDay())->count();
            } catch (\Exception) {
                return 0;
            }
        });

        // ── Pending articles ──────────────────────────────────────────────
        $pendingArticles = Cache::remember('sys.pending_articles', 30, function () {
            return \App\Models\Article::pendingReview()->count();
        });

        return [
            Stat::make('Database', $dbStatus)
                ->description(config('database.default') . ' driver')
                ->color($dbColor)
                ->icon('heroicon-o-circle-stack'),

            Stat::make('PHP Version', PHP_VERSION)
                ->description('Laravel ' . app()->version())
                ->color('primary')
                ->icon('heroicon-o-code-bracket'),

            Stat::make('Queue (pending)', $queueCount)
                ->description('Jobs waiting to be processed')
                ->color($queueCount > 10 ? 'warning' : 'success')
                ->icon('heroicon-o-queue-list'),

            Stat::make('Storage Free', $freeMB . ' MB')
                ->description('Available in storage/')
                ->color($freeMB < 500 ? 'danger' : 'success')
                ->icon('heroicon-o-server'),

            Stat::make('Activity (24h)', $activityCount)
                ->description('Log events in the last 24 hours')
                ->color('primary')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Pending Articles', $pendingArticles)
                ->description('Awaiting admin review')
                ->color($pendingArticles > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-document-text'),
        ];
    }
}
