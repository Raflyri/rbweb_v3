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
            'optimize:clear',
            'storage:link',
            'config:cache',
            'route:cache',
            'view:cache',
        ];

        if (!in_array($command, $allowedCommands)) {
            return response()->json(['error' => 'Command not allowed'], 403);
        }

        try {
            $parameters = [];
            if ($command === 'migrate') {
                $parameters = ['--force' => true];
            }
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
