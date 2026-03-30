<?php

namespace App\Console\Commands;

use App\Models\AuthenticationLog;
use Illuminate\Console\Command;

class PurgeOldAuthLogs extends Command
{
    /**
     * Purge authentication log entries older than 60 days.
     * Designed to run daily via the Laravel scheduler.
     */
    protected $signature   = 'auth-logs:purge {--days=60 : Number of days to retain}';
    protected $description = 'Purge authentication log entries older than the retention period (default: 60 days)';

    public function handle(): int
    {
        $days    = (int) $this->option('days');
        $cutoff  = now()->subDays($days);
        $deleted = AuthenticationLog::where('logged_at', '<', $cutoff)->delete();

        $this->info("✅ Purged {$deleted} authentication log entries older than {$days} days.");

        return self::SUCCESS;
    }
}
