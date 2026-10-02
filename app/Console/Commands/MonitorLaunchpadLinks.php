<?php

namespace App\Console\Commands;

use App\Models\LaunchpadLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitorLaunchpadLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbeverything:monitor-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor uptime for active Launchpad Links';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $links = LaunchpadLink::where('is_active', true)
            ->where('is_monitored', true)
            ->get();

        $this->info("Starting monitoring for {$links->count()} links...");

        foreach ($links as $link) {
            $this->line("Pinging: {$link->url}");
            $startTime = microtime(true);

            try {
                // Ignore SSL errors for local development if needed, timeout after 10 seconds
                $response = Http::timeout(10)->get($link->url);
                $endTime = microtime(true);

                $responseTimeMs = round(($endTime - $startTime) * 1000);

                if ($response->successful() || $response->isRedirect()) {
                    $status = 'up';
                    $this->info("[UP] {$link->url} ({$responseTimeMs}ms)");
                } else {
                    $status = 'down';
                    $this->error("[DOWN] {$link->url} (HTTP {$response->status()})");
                }
            } catch (\Exception $e) {
                $endTime = microtime(true);
                $responseTimeMs = round(($endTime - $startTime) * 1000);
                
                $status = 'timeout';
                $this->error("[TIMEOUT/ERROR] {$link->url} - " . $e->getMessage());
            }

            $link->update([
                'monitoring_status'  => $status,
                'last_checked_at'    => now(),
                'http_response_time' => $responseTimeMs,
            ]);
        }

        $this->info('Monitoring completed.');
    }
}
