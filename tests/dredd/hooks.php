<?php

/**
 * @file
 *
 * Hooks for running Dredd tests.
 */

use Dredd\Hooks;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;

$client = new GuzzleHttp\Client([
    'base_uri' => 'http://0.0.0.0:8080',
    'headers' => [
        'Authorization' => 'Bearer test-user',
    ],
]);

/**
 * Replace elements in requested path.
 */
$pathReplace = function ($transaction, $from, $to) {
    $replacements = [$from => $to];
    $transaction->request->uri = strtr($transaction->request->uri, $replacements);
    $transaction->fullPath = strtr($transaction->fullPath, $replacements);
    // Also fix the ID so the user can see the change.
    $transaction->id = strtr($transaction->id, $replacements);
};

/* @var \Laravel\Lumen\Application $app */
$app = require __DIR__ . '/../../bootstrap/app.php';
$artisan = $app->make(ConsoleKernel::class);

Hooks::beforeAll(function (&$transaction) use ($artisan) {
    $artisan->call('migrate:fresh');
    // Print the resulting output so it is picked out by Dredd for debugging.
    echo $artisan->output();
});

Hooks::beforeEach(function ($transaction) {
    $transaction->request->headers->Authorization = 'Bearer test-user';

    // Skip internal error responses, we can't trigger those and HEAD requests
    // which dredd doesn't support.
    if (preg_match('/(500|HEAD > \d{3})$/', $transaction->name)) {
        $transaction->skip = true;
    }
});

// Change list id to trigger 404.
Hooks::before('/list/{listId} > GET > 404', function (&$transaction) use ($pathReplace) {
    $pathReplace($transaction, 'default', 'bad-value');
});

// Make sure list doesn't exist.
Hooks::before('/list/{listId}/{materialId} > PUT > 404', function (&$transaction) use ($pathReplace) {
    $pathReplace($transaction, 'default', 'bad-value');
});

// Change to bad material id.
Hooks::before('/list/{listId} > GET > 422', function (&$transaction) use ($pathReplace) {
    $pathReplace($transaction, '870970-basis%3A54871910', 'bad-materialId');
});

// Change to bad material id.
Hooks::before('/list/{listId}/{materialId} > PUT > 422', function (&$transaction) use ($pathReplace) {
    $pathReplace($transaction, '870970-basis%3A54871910', 'bad-materialId');
});

// Change to bad material id.
Hooks::before('/list/{listId}/{materialId} > DELETE > 422', function (&$transaction) use ($pathReplace) {
    $pathReplace($transaction, '870970-basis%3A54871910', 'bad-materialId');
});
