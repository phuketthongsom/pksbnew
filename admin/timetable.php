<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

$admin_page_title = 'Edit Timetable';
$admin_active_nav = 'timetable';

$routes = array_filter(load_json('routes.json'), fn($r) => !empty($r['active']));
$tt     = load_json('timetable.json');

$msg = $err = '';

// ─── Save ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'save_trips') {
        $tid    = $_POST['table_id'] ?? '';
        $trips_raw = $_POST['trips'] ?? [];
        if (!isset($tt[$tid])) { $err = 'Unknown timetable key.'; goto done; }

        $stop_ids = array_keys($tt[$tid]['trips'][0] ?? []);
        $new_trips = [];
        foreach ($trips_raw as $trip) {
            $row = [];
            foreach ($stop_ids as $sid) {
                $row[$sid] = preg_match('/^\d{2}:\d{2}$/', $trip[$sid] ?? '') ? $trip[$sid] : ($trip[$sid] ?? '');
            }
            $new_trips[] = $row;
        }
        $tt[$tid]['trips'] = $new_trips;
        $tt['last_updated'] = date('Y-m-d');
        save_json('timetable.json', $tt);
        $msg = 'Timetable saved.';

    } elseif ($action === 'add_trip') {
        $tid = $_POST['table_id'] ?? '';
        if (isset($tt[$tid]['trips'][0])) {
            $blank = array_fill_keys(array_keys($tt[$tid]['trips'][0]), '');
            $tt[$tid]['trips'][] = $blank;
            $tt['last_updated'] = date('Y-m-d');
            save_json('timetable.json', $tt);
            $msg = 'New trip row added.';
        }

    } elseif ($action === 'delete_trip') {
        $tid = $_POST['table_id'] ?? '';
        $idx = (int)($_POST['trip_index'] ?? -1);
        if ($idx >= 0 && isset($tt[$tid]['trips'][$idx])) {
            array_splice($tt[$tid]['trips'], $idx, 1);
            $tt['last_updated'] = date('Y-m-d');
            save_json('timetable.json', $tt);
            $msg = 'Trip deleted.';
        }
    }
    done:
}

require_once __DIR__ . '/inc/admin_header.php';

// Build list of table IDs
$tables = [];
foreach ($routes as $r) {
    if (!empty($tt[$r['id']]))          $tables[$r['id']]           = t($tt[$r['id']]['label']);
    if (!empty($tt[$r['id'].'_return']))$tables[$r['id'].'_return'] = t($tt[$r['id'].'_return']['label']);
}
$active_tid = $_GET['tid'] ?? array_key_first($tables);
?>

<?php if ($msg): ?><div class="alert alert-success"><?= esc($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= esc($err) ?></div><?php endif; ?>

<!-- Tab switcher -->
<div class="timetable-tabs" style="margin-bottom:20px">
  <?php foreach ($tables as $tid => $tlabel): ?>
  <a href="?tid=<?= urlencode($tid) ?>" class="tab-btn <?= $active_tid===$tid?'active':'' ?>">
    <?= esc($tlabel) ?>
  </a>
  <?php endforeach; ?>
</div>

<?php
$ttd = $tt[$active_tid] ?? null;
if ($ttd && !empty($ttd['trips'])):
    $stop_ids = array_keys($ttd['trips'][0]);
    // Get stop name map
    $base_rid = preg_replace('/_return$/', '', $active_tid);
    $stop_names = [];
    foreach ($routes as $r) {
        if ($r['id'] === $base_rid) {
            foreach ($r['stops'] as $s) $stop_names[$s['id']] = $s['name']['en'];
            break;
        }
    }
?>

<div class="admin-card">
  <h2>
    <?= esc($ttd['label']['en'] ?? $active_tid) ?>
    <span style="font-size:.78rem;font-weight:400;color:#888;margin-left:8px">Last updated: <?= esc($tt['last_updated'] ?? '—') ?></span>
  </h2>

  <form method="post">
    <input type="hidden" name="action" value="save_trips">
    <input type="hidden" name="table_id" value="<?= esc($active_tid) ?>">

    <div style="overflow-x:auto">
      <table class="admin-table" style="min-width:600px">
        <thead>
          <tr>
            <th>#</th>
            <?php foreach ($stop_ids as $sid): ?>
            <th><?= esc($stop_names[$sid] ?? ucfirst(str_replace('_',' ',$sid))) ?></th>
            <?php endforeach; ?>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ttd['trips'] as $i => $trip): ?>
          <tr>
            <td style="color:#999;font-size:.82rem"><?= $i+1 ?></td>
            <?php foreach ($stop_ids as $sid): ?>
            <td>
              <input type="text" name="trips[<?= $i ?>][<?= esc($sid) ?>]"
                     value="<?= esc($trip[$sid] ?? '') ?>"
                     style="width:58px;padding:4px 6px;border:1px solid #ddd;border-radius:4px;font-size:.85rem;text-align:center"
                     placeholder="HH:MM">
            </td>
            <?php endforeach; ?>
            <td>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this trip?')">
                <input type="hidden" name="action" value="delete_trip">
                <input type="hidden" name="table_id" value="<?= esc($active_tid) ?>">
                <input type="hidden" name="trip_index" value="<?= $i ?>">
                <button type="submit" style="background:none;border:none;color:#e74c3c;cursor:pointer;font-size:.85rem">🗑 Del</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Save Changes</button>
      <a href="../timetable.php" target="_blank" class="btn btn-sm" style="background:#f0f0f0;color:#333;margin-left:8px">🔍 Preview Timetable</a>
    </div>
  </form>

  <!-- Add trip -->
  <form method="post" style="margin-top:12px">
    <input type="hidden" name="action" value="add_trip">
    <input type="hidden" name="table_id" value="<?= esc($active_tid) ?>">
    <button type="submit" class="btn btn-sm" style="background:#f0f0f0;color:#333">＋ Add Trip Row</button>
  </form>
</div>

<?php else: ?>
<div class="admin-card"><p style="color:#888">No timetable data for this direction.</p></div>
<?php endif; ?>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
