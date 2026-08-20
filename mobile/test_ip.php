<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client(['timeout' => 3, 'http_errors' => false]);

echo "Testing 127.0.0.1:8000...\n";
try {
    $t1 = microtime(true);
    $r1 = $client->get('http://127.0.0.1:8000/api/v1/auth/me');
    echo "127.0.0.1 status: " . $r1->getStatusCode() . " in " . round(microtime(true) - $t1, 3) . "s\n";
} catch (\Exception $e) {
    echo "127.0.0.1 error: " . $e->getMessage() . "\n";
}

echo "Testing 192.168.1.32:8000...\n";
try {
    $t2 = microtime(true);
    $r2 = $client->get('http://192.168.1.32:8000/api/v1/auth/me');
    echo "192.168.1.32 status: " . $r2->getStatusCode() . " in " . round(microtime(true) - $t2, 3) . "s\n";
} catch (\Exception $e) {
    echo "192.168.1.32 error: " . $e->getMessage() . "\n";
}
