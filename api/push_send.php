<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();
require_once __DIR__ . '/../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

header('Content-Type: application/json');

$title = trim($_POST['title'] ?? '');
$body  = trim($_POST['body']  ?? '');
$url   = trim($_POST['url']   ?? '/');

if (!$title || !$body) {
    echo json_encode(['error' => 'title and body required']); exit;
}

$subs_data     = load_json('push_subscriptions.json');
$subscriptions = $subs_data['subscriptions'] ?? [];

if (empty($subscriptions)) {
    echo json_encode(['sent' => 0, 'msg' => 'No subscribers yet']); exit;
}

$auth = [
    'VAPID' => [
        'subject'    => 'mailto:admin@phuketsmartbus.com',
        'publicKey'  => 'BEEytUsCkMQedtwVx0Ej8KDPoyTL28D828R8drEJmHGlNFBD0cvoYysRc2Zfb8MhbZMrB2PpLjuDOhgvi4iU9So',
        'privateKey' => 'FW846LObinAWvfnSL5PMdW7m_Ca5oYNpvLwL-N_XX3w',
    ]
];

$webPush = new WebPush($auth);
$payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url]);

foreach ($subscriptions as $sub) {
    $webPush->queueNotification(Subscription::create($sub), $payload);
}

$sent   = 0;
$failed = [];

foreach ($webPush->flush() as $result) {
    if ($result->isSuccess()) {
        $sent++;
    } else {
        $failed[] = $result->getEndpoint();
    }
}

// Remove expired/invalid subscriptions
if ($failed) {
    $subs_data['subscriptions'] = array_values(array_filter(
        $subs_data['subscriptions'],
        fn($s) => !in_array($s['endpoint'], $failed)
    ));
    save_json('push_subscriptions.json', $subs_data);
}

echo json_encode(['sent' => $sent, 'failed' => count($failed)]);
