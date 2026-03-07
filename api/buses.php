<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../inc/helpers.php';

$token   = gps_token();
$base    = 'https://trackback.gpsiam.net';
$timeout = 8;

$ch = curl_init($base . '/a/device/interval');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => '{}',
    CURLOPT_TIMEOUT        => $timeout,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ],
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error || $http_code !== 200) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => $curl_error ?: "HTTP $http_code"]);
    exit;
}

$data = json_decode($response, true);

if (empty($data['ok']) || empty($data['devices'])) {
    echo json_encode(['ok' => false, 'buses' => [], 'error' => 'No device data']);
    exit;
}

$buses = array_map(function($d) {
    return [
        'name'     => $d['name'] ?? '',
        'lat'      => (float)($d['latitude']  ?? 0),
        'lng'      => (float)($d['longitude'] ?? 0),
        'speed'    => (float)($d['speed']     ?? 0),
        'heading'  => (int)($d['heading']     ?? 0),
        'status'   => $d['status']['name'] ?? $d['status'] ?? 'UNKNOWN',
        'time'     => $d['time'] ?? '',
    ];
}, $data['devices']);

echo json_encode(['ok' => true, 'buses' => $buses, 'fetched_at' => date('Y-m-d H:i:s')]);
