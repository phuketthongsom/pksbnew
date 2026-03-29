<?php
require_once __DIR__ . '/../inc/helpers.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error'=>'method not allowed']); exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (empty($body['endpoint'])) {
    http_response_code(400); echo json_encode(['error'=>'invalid subscription']); exit;
}

$data = load_json('push_subscriptions.json');
if (!isset($data['subscriptions'])) $data['subscriptions'] = [];

// Upsert by endpoint
$endpoint = $body['endpoint'];
$found = false;
foreach ($data['subscriptions'] as &$s) {
    if ($s['endpoint'] === $endpoint) { $s = $body; $found = true; break; }
}
if (!$found) $data['subscriptions'][] = $body;

save_json('push_subscriptions.json', $data);
echo json_encode(['ok' => true, 'total' => count($data['subscriptions'])]);
