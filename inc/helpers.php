<?php
// ─── Data helpers ────────────────────────────────────────────────────────────

define('DATA_DIR', __DIR__ . '/../data/');

function load_json(string $file): array {
    $path = DATA_DIR . $file;
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return $data ?? [];
}

function save_json(string $file, $data): bool {
    $path = DATA_DIR . $file;
    return (bool) file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ─── Output helpers ───────────────────────────────────────────────────────────

function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Determine root path
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Walk up to root (remove /admin subfolder if present)
    $root = rtrim(preg_replace('#/admin$#', '', $script), '/');
    return $proto . '://' . $host . $root . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return base_url('assets/' . ltrim($path, '/'));
}

// ─── Language helpers ─────────────────────────────────────────────────────────

function lang(): string {
    $g = $_GET['lang'] ?? '';
    if (in_array($g, ['th','en'])) return $g;
    return $_SESSION['lang'] ?? 'th';
}

function t(array $strings): string {
    $l = lang();
    return $strings[$l] ?? $strings['en'] ?? $strings['th'] ?? '';
}

// ─── Activity logger ──────────────────────────────────────────────────────────

function log_event(string $type, string $label, string $id = ''): void {
    $path = DATA_DIR . 'logs.json';
    $data = file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];
    $events = $data['events'] ?? [];
    $events[] = [
        'ts'   => time(),
        'type' => $type,
        'label'=> $label,
        'id'   => $id,
        'lang' => $_GET['lang'] ?? $_SESSION['lang'] ?? 'th',
    ];
    // Keep last 1000 entries
    if (count($events) > 1000) $events = array_slice($events, -1000);
    file_put_contents($path, json_encode(['events' => $events], JSON_UNESCAPED_UNICODE));
}

// ─── GPS helpers ──────────────────────────────────────────────────────────────

function gps_token(): string {
    return 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1aWQiOjU4NjIxLCJkYXRhIjp7InBoYXNoIjoyNTM5OTgyOTc2fSwiZXhwIjoxODA0MTQ3NTI2fQ.NCOu4i-JUX2fRXopnh8qQQ728TEiH-m6z7eHzsU1dWE';
}

function gps_status_label(string $status): string {
    $map = [
        'TRACKING_STATUS_RUN'  => 'Running',
        'TRACKING_STATUS_PARK' => 'Parked',
        'TRACKING_STATUS_OFF'  => 'Offline',
        'TRACKING_STATUS_STOP' => 'Stopped',
    ];
    return $map[$status] ?? $status;
}

function gps_status_color(string $status): string {
    $map = [
        'TRACKING_STATUS_RUN'  => '#27ae60',
        'TRACKING_STATUS_PARK' => '#f39c12',
        'TRACKING_STATUS_OFF'  => '#e74c3c',
        'TRACKING_STATUS_STOP' => '#95a5a6',
    ];
    return $map[$status] ?? '#95a5a6';
}
