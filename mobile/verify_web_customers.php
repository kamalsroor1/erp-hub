<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$jar = new CookieJar();
$client = new Client(['cookies' => $jar, 'http_errors' => false]);

// 1. Login
$loginRes = $client->post('http://127.0.0.1:8080/login', [
    'form_params' => [
        'login' => '01012316954',
        'password' => 'password',
    ],
    'headers' => ['Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9'],
]);

// 2. Fetch Customers
$res = $client->get('http://127.0.0.1:8080/customers');
$html = (string)$res->getBody();

if (preg_match('/data-page="([^"]+)"/', $html, $matches)) {
    $json = json_decode(htmlspecialchars_decode($matches[1]), true);
} else {
    $json = [];
}

echo "Status Code: " . $res->getStatusCode() . "\n";
echo "Customers Count: " . count($json['props']['customers'] ?? []) . "\n";
echo "Total Receivable: " . ($json['props']['summary']['total_receivable'] ?? '0') . " ج.م\n";

foreach ($json['props']['customers'] ?? [] as $c) {
    echo "  • " . $c['name'] . " | الهاتف: " . $c['phone'] . " | الرصيد: " . $c['current_balance'] . " ج.م\n";
}
