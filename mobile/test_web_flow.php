<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$jar = new CookieJar();
$client = new Client(['cookies' => $jar, 'http_errors' => false]);

// 1. Get Login Page & CSRF Token
$getLogin = $client->get('http://127.0.0.1:8080/login');
$loginHtml = (string)$getLogin->getBody();

// Extract CSRF
$token = '';
if (preg_match('/name="_token" value="([^"]+)"/', $loginHtml, $m)) {
    $token = $m[1];
} elseif (preg_match('/"csrf_token":"([^"]+)"/', $loginHtml, $m)) {
    $token = $m[1];
}

$xsrf = $jar->getCookieByName('XSRF-TOKEN')?->getValue();
if ($xsrf) {
    $xsrf = urldecode($xsrf);
}

// 2. Post Login with Inertia Header
$loginRes = $client->post('http://127.0.0.1:8080/login', [
    'json' => [
        'login' => '01012316954',
        'password' => 'password',
    ],
    'headers' => [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-XSRF-TOKEN' => $xsrf,
    ],
]);

echo "Login status: " . $loginRes->getStatusCode() . "\n";

// 3. Get Customers
$res = $client->get('http://127.0.0.1:8080/customers', [
    'headers' => [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
    ],
]);

echo "Customers response status: " . $res->getStatusCode() . "\n";
$data = json_decode((string)$res->getBody(), true);
echo "Customers count: " . count($data['props']['customers'] ?? []) . "\n";
echo "Total Receivable: " . ($data['props']['summary']['total_receivable'] ?? '0') . " ج.م\n";

foreach ($data['props']['customers'] ?? [] as $c) {
    echo "  • " . $c['name'] . " | " . $c['phone'] . " | الرصيد: " . $c['current_balance'] . " ج.م\n";
}
