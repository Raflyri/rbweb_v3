<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class EmergencyCommandController extends Controller
{
    public function run(Request $request)
    {
        $token = $request->input('token');
        $validToken = env('EMERGENCY_ROUTE_TOKEN');

        if (!$validToken || $token !== $validToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $command = $request->input('command');
        $allowedCommands = [
            'migrate',
            'db:seed',
            'optimize:clear',
            'storage:link',
            'config:cache',
            'route:cache',
            'view:cache',
            'app:publish-scheduled-articles',
            'articles:cleanup-test-data',
            'articles:fix-locale-keys',
        ];

        if (!in_array($command, $allowedCommands)) {
            return response()->json(['error' => 'Command not allowed'], 403);
        }

        try {
            // Anything interactive must be given --force here: there is no TTY
            // behind an HTTP request, so a confirmation prompt would hang.
            $parameters = match ($command) {
                'migrate', 'db:seed', 'articles:fix-locale-keys' => ['--force' => true],
                'articles:cleanup-test-data' => [
                    '--force' => true,
                    '--mode'  => in_array($request->input('mode'), ['draft', 'delete'], true)
                        ? $request->input('mode')
                        : 'draft',
                ],
                default => [],
            };
            Artisan::call($command, $parameters);
            $output = Artisan::output();
            Log::info("Emergency command executed: {$command}", ['output' => $output]);
            return response()->json([
                'status' => 'success',
                'message' => "Command {$command} executed successfully.",
                'output' => $output
            ]);
        } catch (\Exception $e) {
            Log::error("Emergency command failed: {$command}", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
