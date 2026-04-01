<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Compile manually
$content = file_get_contents('resources/views/blog/show.blade.php');
$compiled = app('blade.compiler')->compileString($content);

// Try to parse the compiled PHP using a linter approach or eval
echo "Compiled!\n" . substr($compiled, 0, 100);
$tmpFile = __DIR__ . '/test_compiled.php';
file_put_contents($tmpFile, $compiled);
exec("php -l " . escapeshellarg($tmpFile), $out, $ret);
echo implode("\n", $out);
