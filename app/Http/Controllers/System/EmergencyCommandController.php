<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Run a small, fixed set of artisan commands over HTTP.
 *
 * This is the only route to artisan on this shared host — there is no usable
 * SSH — so its failure modes matter more than usual:
 *
 *  - The token is read via config(), never env(). 'config:cache' is on the
 *    allowlist below, and after it runs env() returns null for everything;
 *    reading the token from env() would have bricked this endpoint for good.
 *  - Comparison is constant time, so the token cannot be recovered by timing.
 *  - Failed attempts are logged, not just successful ones.
 *  - The route is rate limited (see routes/web.php).
 */
class EmergencyCommandController extends Controller
{
    /**
     * Commands that may be triggered over HTTP.
     *
     * Keep this list short and boring. Anything added here is reachable by
     * anyone holding the token.
     */
    protected const ALLOWED_COMMANDS = [
        'migrate',
        'db:seed',
        'optimize:clear',
        'storage:link',
        'config:cache',
        'route:cache',
        'view:cache',
        'app:publish-scheduled-articles',
        'app:generate-sitemap',
        'app:check-seo-config',
        'articles:cleanup-test-data',
        'articles:fix-locale-keys',
    ];

    public function run(Request $request)
    {
        $token      = (string) $request->input('token');
        $validToken = (string) config('app.emergency_token');

        if ($validToken === '' || ! hash_equals($validToken, $token)) {
            Log::warning('Emergency command rejected: bad or missing token', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'command'    => $request->input('command'),
                'reason'     => $validToken === '' ? 'no token configured' : 'token mismatch',
            ]);

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $command = (string) $request->input('command');

        if (! in_array($command, static::ALLOWED_COMMANDS, true)) {
            Log::warning('Emergency command rejected: command not allowed', [
                'ip'      => $request->ip(),
                'command' => $command,
            ]);

            return response()->json(['error' => 'Command not allowed'], 403);
        }

        try {
            Artisan::call($command, $this->parametersFor($command, $request));
            $output = Artisan::output();

            Log::info("Emergency command executed: {$command}", [
                'ip'     => $request->ip(),
                'output' => $output,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => "Command {$command} executed successfully.",
                'output'  => $output,
            ]);
        } catch (\Throwable $e) {
            Log::error("Emergency command failed: {$command}", [
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Anything interactive needs --force: an HTTP request has no TTY, so a
     * confirmation prompt would hang the worker instead of asking anyone.
     *
     * @return array<string, mixed>
     */
    protected function parametersFor(string $command, Request $request): array
    {
        return match ($command) {
            'migrate', 'db:seed', 'articles:fix-locale-keys' => ['--force' => true],
            'articles:cleanup-test-data' => [
                '--force' => true,
                '--mode'  => in_array($request->input('mode'), ['draft', 'delete'], true)
                    ? $request->input('mode')
                    : 'draft',
            ],
            default => [],
        };
    }
}
