<?php

/**
 * @file
 *
 * Hooks for running Dredd tests.
 */

use Dredd\Hooks;
use Guzzle\Client;

$client = new GuzzleHttp\Client([
    'base_uri' => 'http://localhost:8080',
    'headers' => [
        'Authorization' => 'Bearer test-user',
    ],
]);

/**
 * Replace elements in requested path.
 */
function pathReplace($transaction, $from, $to)
{
    $replacements = [$from => $to];
    $transaction->request->uri = strtr($transaction->request->uri, $replacements);
    $transaction->fullPath = strtr($transaction->fullPath, $replacements);
    // Also fix the ID so the user can see the change.
    $transaction->id = strtr($transaction->id, $replacements);
}

Hooks::beforeEach(function ($transaction) {
    $transaction->request->headers->Authorization = 'Bearer test-user';

    // Skip internal error responses, we can't trigger those.
    if (preg_match('/500$/', $transaction->name)) {
        $transaction->skip = true;
    }
});

// Change list id to trigger 404.
Hooks::before('/list/{listId} > GET > 404', function (&$transaction) {
    pathReplace($transaction, 'default', 'bad-value');
});

// Ensure material exists.
Hooks::before('/list/{listId}/{materialId} > GET > 201', function (&$transaction) use ($client) {
    $client->put('/list/default/870970-basis%3A54871910-test-get', []);

    // Change the path to not clobber other tests.
    pathReplace($transaction, '870970-basis%3A54871910', '870970-basis%3A54871910-test-get');
});

// Make sure list doesn't exist.
Hooks::before('/list/{listId}/{materialId} > PUT > 404', function (&$transaction) use ($client) {
    pathReplace($transaction, 'default', 'bad-value');
});
