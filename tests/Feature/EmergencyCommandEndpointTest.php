<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| POST /system/emergency-command
|--------------------------------------------------------------------------
| The only artisan path on this host. It must stay reachable with the right
| token, closed to everything else, and noisy in the log when probed.
*/

beforeEach(function () {
    // The rate limiter is backed by the array cache in tests, which persists
    // across tests in the same process.
    Cache::flush();
    config(['app.emergency_token' => 'correct-horse-battery-staple']);
});

function callEndpoint(array $payload)
{
    return test()->postJson('/system/emergency-command', $payload);
}

it('runs an allowed command with the correct token', function () {
    callEndpoint(['token' => 'correct-horse-battery-staple', 'command' => 'optimize:clear'])
        ->assertOk()
        ->assertJsonPath('status', 'success');
});

it('runs the scheduled-publish command through the endpoint', function () {
    callEndpoint(['token' => 'correct-horse-battery-staple', 'command' => 'app:publish-scheduled-articles'])
        ->assertOk()
        ->assertJsonPath('status', 'success');
});

it('rejects a wrong token with 401', function () {
    callEndpoint(['token' => 'wrong', 'command' => 'optimize:clear'])
        ->assertStatus(401)
        ->assertJsonPath('error', 'Unauthorized');
});

it('rejects a missing token with 401', function () {
    callEndpoint(['command' => 'optimize:clear'])->assertStatus(401);
});

it('rejects every request when no token is configured, rather than letting all through', function () {
    config(['app.emergency_token' => null]);

    callEndpoint(['token' => '', 'command' => 'optimize:clear'])->assertStatus(401);
});

it('rejects a command that is not on the allowlist with 403', function () {
    callEndpoint(['token' => 'correct-horse-battery-staple', 'command' => 'tinker'])
        ->assertStatus(403)
        ->assertJsonPath('error', 'Command not allowed');
});

it('logs a warning when the token is wrong', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message) => str_contains($message, 'bad or missing token'));

    Log::shouldReceive('info')->zeroOrMoreTimes();
    Log::shouldReceive('error')->zeroOrMoreTimes();

    callEndpoint(['token' => 'wrong', 'command' => 'optimize:clear'])->assertStatus(401);
});

it('rate limits repeated attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        callEndpoint(['token' => 'wrong', 'command' => 'optimize:clear'])->assertStatus(401);
    }

    callEndpoint(['token' => 'wrong', 'command' => 'optimize:clear'])->assertStatus(429);
});

it('still reads the token after config:cache has run', function () {
    // The original bug: the controller read env('EMERGENCY_ROUTE_TOKEN'), and
    // config:cache — itself on the allowlist — makes env() return null,
    // permanently locking the only artisan path on this host.
    expect(config('app.emergency_token'))->toBe('correct-horse-battery-staple');

    $source = file_get_contents(app_path('Http/Controllers/System/EmergencyCommandController.php'));

    expect($source)->not->toContain("env('EMERGENCY_ROUTE_TOKEN')")
        ->and($source)->toContain("config('app.emergency_token')")
        ->and($source)->toContain('hash_equals');
});
