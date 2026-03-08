<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

$admin_page_title = 'Activity Logs';
$admin_active_nav = 'logs';

$log_data = load_json('logs.json');
$events   = array_reverse($log_data['events'] ?? []);

// Clear action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear') {
    save_json('logs.json', ['events' => []]);
    header('Location: logs.php'); exit;
}

// Filter
$filter = $_GET['type'] ?? 'all';
if ($filter !== 'all') {
    $events = array_values(array_filter($events, fn($e) => $e['type'] === $filter));
}

// Stats (always from full unfiltered set)
$all_events = $log_data['events'] ?? [];
$today      = strtotime('today');
$today_count = count(array_filter($all_events, fn($e) => $e['ts'] >= $today));
$week_count  = count(array_filter($all_events, fn($e) => $e['ts'] >= strtotime('-7 days')));

// Top items per type
function top_items(array $events, string $type, int $n = 3): array {
    $counts = [];
    foreach ($events as $e) {
        if ($e['type'] !== $type) continue;
        $counts[$e['label']] = ($counts[$e['label']] ?? 0) + 1;
    }
    arsort($counts);
    return array_slice($counts, 0, $n, true);
}
$top_routes     = top_items($all_events, 'route');
$top_attractions = top_items($all_events, 'attraction');
$top_stops      = top_items($all_events, 'stop');

$type_meta = [
    'route'      => ['icon' => '🗺', 'label' => 'Route',      'color' => '#003087'],
    'attraction' => ['icon' => '🏖️', 'label' => 'Attraction', 'color' => '#01aaa8'],
    'stop'       => ['icon' => '📍', 'label' => 'Stop',       'color' => '#e67e22'],
];

function time_ago(int $ts): string {
    $diff = time() - $ts;
    if ($diff < 60)   return $diff . 's ago';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    return date('d M', $ts);
}

require_once __DIR__ . '/inc/admin_header.php';
?>

<style>
.log-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px}
.log-stat{background:#fff;border-radius:10px;padding:16px 20px;box-shadow:0 1px 4px rgba(0,0,0,.07)}
.log-stat-val{font-size:2rem;font-weight:800;color:#1a1a2e;line-height:1}
.log-stat-lbl{font-size:.78rem;color:#888;margin-top:4px}
.log-top{font-size:.82rem;color:#555;margin-top:6px;line-height:1.6}
.log-top span{font-weight:600;color:#1a1a2e}
.log-filters{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.log-filter-btn{padding:5px 14px;border-radius:20px;font-size:.82rem;font-weight:600;cursor:pointer;border:1px solid #ddd;background:#fff;color:#555;text-decoration:none}
.log-filter-btn.active{background:#1a1a2e;color:#fff;border-color:#1a1a2e}
.log-table{width:100%;border-collapse:collapse;font-size:.84rem}
.log-table th{text-align:left;padding:8px 12px;font-size:.75rem;font-weight:700;color:#888;border-bottom:2px solid #eee;white-space:nowrap}
.log-table td{padding:9px 12px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
.log-table tr:hover td{background:#fafafa}
.log-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:12px;font-size:.75rem;font-weight:700;color:#fff}
.log-lang{display:inline-block;padding:1px 7px;border-radius:8px;font-size:.72rem;font-weight:700;background:#eee;color:#555}
.log-time{color:#aaa;font-size:.78rem;white-space:nowrap}
.log-name{font-weight:600;color:#1a1a2e}
.log-id{color:#aaa;font-size:.75rem;margin-left:6px}
.log-empty{text-align:center;padding:40px;color:#aaa;font-size:.9rem}
</style>

<!-- Stats -->
<div class="log-stats">
  <div class="log-stat">
    <div class="log-stat-val"><?= $today_count ?></div>
    <div class="log-stat-lbl">Views today</div>
  </div>
  <div class="log-stat">
    <div class="log-stat-val"><?= $week_count ?></div>
    <div class="log-stat-lbl">Views last 7 days</div>
  </div>
  <div class="log-stat">
    <div class="log-stat-val"><?= count($all_events) ?></div>
    <div class="log-stat-lbl">Total logged</div>
  </div>
  <div class="log-stat">
    <div class="log-stat-lbl" style="margin-top:0;font-weight:700;color:#1a1a2e;margin-bottom:4px">🗺 Top Routes</div>
    <div class="log-top">
      <?php foreach ($top_routes as $name => $cnt): ?>
      <div><span><?= htmlspecialchars($name) ?></span> — <?= $cnt ?>×</div>
      <?php endforeach; ?>
      <?php if (!$top_routes): ?><span style="color:#ccc">No data</span><?php endif; ?>
    </div>
  </div>
  <div class="log-stat">
    <div class="log-stat-lbl" style="margin-top:0;font-weight:700;color:#1a1a2e;margin-bottom:4px">🏖️ Top Attractions</div>
    <div class="log-top">
      <?php foreach ($top_attractions as $name => $cnt): ?>
      <div><span><?= htmlspecialchars($name) ?></span> — <?= $cnt ?>×</div>
      <?php endforeach; ?>
      <?php if (!$top_attractions): ?><span style="color:#ccc">No data</span><?php endif; ?>
    </div>
  </div>
  <div class="log-stat">
    <div class="log-stat-lbl" style="margin-top:0;font-weight:700;color:#1a1a2e;margin-bottom:4px">📍 Top Stops</div>
    <div class="log-top">
      <?php foreach ($top_stops as $name => $cnt): ?>
      <div><span><?= htmlspecialchars($name) ?></span> — <?= $cnt ?>×</div>
      <?php endforeach; ?>
      <?php if (!$top_stops): ?><span style="color:#ccc">No data</span><?php endif; ?>
    </div>
  </div>
</div>

<!-- Filters + Clear -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px">
  <div class="log-filters">
    <a href="logs.php" class="log-filter-btn <?= $filter==='all'?'active':'' ?>">All (<?= count($all_events) ?>)</a>
    <?php foreach ($type_meta as $key => $m):
      $cnt = count(array_filter($all_events, fn($e) => $e['type'] === $key));
    ?>
    <a href="logs.php?type=<?= $key ?>" class="log-filter-btn <?= $filter===$key?'active':'' ?>">
      <?= $m['icon'] ?> <?= $m['label'] ?> (<?= $cnt ?>)
    </a>
    <?php endforeach; ?>
  </div>
  <form method="post" onsubmit="return confirm('Clear all logs?')">
    <input type="hidden" name="action" value="clear">
    <button class="btn btn-sm" style="background:#ffeaea;color:#c0392b;border:1px solid #f5c6c6">🗑 Clear All Logs</button>
  </form>
</div>

<!-- Table -->
<?php if ($events): ?>
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden">
  <table class="log-table">
    <thead>
      <tr>
        <th>TIME</th>
        <th>TYPE</th>
        <th>NAME</th>
        <th>LANG</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (array_slice($events, 0, 200) as $e):
        $m = $type_meta[$e['type']] ?? ['icon'=>'•','label'=>$e['type'],'color'=>'#999'];
      ?>
      <tr>
        <td class="log-time"><?= time_ago($e['ts']) ?><br><span style="font-size:.7rem;color:#ccc"><?= date('H:i', $e['ts']) ?></span></td>
        <td><span class="log-badge" style="background:<?= $m['color'] ?>"><?= $m['icon'] ?> <?= $m['label'] ?></span></td>
        <td>
          <span class="log-name"><?= htmlspecialchars($e['label']) ?></span>
          <?php if (!empty($e['id'])): ?>
          <span class="log-id"><?= htmlspecialchars($e['id']) ?></span>
          <?php endif; ?>
        </td>
        <td><span class="log-lang"><?= htmlspecialchars(strtoupper($e['lang'])) ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (count($events) > 200): ?>
  <p style="text-align:center;padding:10px;font-size:.8rem;color:#aaa">Showing 200 of <?= count($events) ?> entries</p>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="log-empty">No activity logged yet. Visit routes, attractions and stops on the public site to generate logs.</div>
<?php endif; ?>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
