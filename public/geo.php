<?php
require_once __DIR__ . '/../vendor/autoload.php';

use GeoIp2\Database\Reader;

$ip = $_GET['ip'] ?? $_SERVER['REMOTE_ADDR'];

try {
    $reader = new Reader(__DIR__ . '/../storage/GeoLite2-Country.mmdb');
    $record = $reader->country($ip);
    $country = $record->country->isoCode;
} catch (Exception $e) {
    $country = "";
}

header('Content-Type: text/plain; charset=utf-8');
echo $country;
