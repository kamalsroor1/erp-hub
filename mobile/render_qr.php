<?php

require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\ConsoleWriter;

$url = 'http://192.168.1.32:8080';
$qrCode = new QrCode($url);
$writer = new ConsoleWriter();
$result = $writer->write($qrCode);

echo PHP_EOL . "📱 رابط التطبيق على هاتفك المحمول (متصل بنفس شبكة الواي فاي):" . PHP_EOL;
echo "➡️ " . $url . PHP_EOL . PHP_EOL;
echo $result->getString() . PHP_EOL;
