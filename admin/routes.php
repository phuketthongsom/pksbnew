<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

$admin_page_title = 'Manage Routes';
$admin_active_nav = 'routes';

$routes = load_json('routes.json');
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_route') {
        $id = $_POST['route_id'] ?? '';
        foreach ($routes as &$r) {
            if ($r['id'] !== $id) continue;
            $r['number']           = trim($_POST['number']);
            $r['color']            = trim($_POST['color']);
            $r['name']['th']       = trim($_POST['name_th']);
            $r['name']['en']       = trim($_POST['name_en']);
            $r['description']['th']= trim($_POST['desc_th']);
            $r['description']['en']= trim($_POST['desc_en']);
            $r['active']           = !empty($_POST['active']);
            $r['circular']         = !empty($_POST['circular']);
            $r['tracking_url']     = trim($_POST['tracking_url'] ?? '');
            // Reorder stops if order submitted
            if (!empty($_POST['stop_order'])) {
                $order = json_decode($_POST['stop_order'], true);
                if (is_array($order)) {
                    $indexed = [];
                    foreach ($r['stops'] as $s) $indexed[$s['id']] = $s;
                    $reordered = [];
                    foreach ($order as $sid) {
                        if (isset($indexed[$sid])) $reordered[] = $indexed[$sid];
                    }
                    $r['stops'] = $reordered;
                }
            }
            break;
        }
        unset($r);
        save_json('routes.json', $routes);
        $msg = 'Route saved.';

    } elseif ($action === 'toggle_active') {
        $id = $_POST['route_id'] ?? '';
        foreach ($routes as &$r) {
            if ($r['id'] === $id) { $r['active'] = !($r['active'] ?? false); break; }
        }
        unset($r);
        save_json('routes.json', $routes);
        $msg = 'Route status updated.';
    }
}

$edit_id = $_GET['edit'] ?? null;
require_once __DIR__ . '/inc/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= esc($msg) ?></div><?php endif; ?>

<!-- Route list -->
<div class="admin-card">
  <h2>All Routes</h2>
  <table class="admin-table">
    <thead><tr><th>Number</th><th>Name (EN)</th><th>Stops</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($routes as $r): ?>
      <tr>
        <td><span style="background:<?= esc($r['color']) ?>;color:#fff;padding:2px 10px;border-radius:4px;font-weight:700"><?= esc($r['number']) ?></span></td>
        <td><?= esc($r['name']['en']) ?></td>
        <td><?= count($r['stops']) ?></td>
        <td>
          <form method="post" style="display:inline">
            <input type="hidden" name="action" value="toggle_active">
            <input type="hidden" name="route_id" value="<?= esc($r['id']) ?>">
            <button type="submit" class="btn btn-sm" style="background:<?= $r['active']?'#27ae60':'#e74c3c' ?>;color:#fff;padding:4px 12px">
              <?= $r['active'] ? 'Active' : 'Inactive' ?>
            </button>
          </form>
        </td>
        <td><a href="?edit=<?= esc($r['id']) ?>" style="color:var(--teal);font-weight:600">Edit ✏️</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Edit form -->
<?php
$edit_route = null;
if ($edit_id) {
    foreach ($routes as $r) { if ($r['id'] === $edit_id) { $edit_route = $r; break; } }
}
if ($edit_route):
?>
<div class="admin-card">
  <h2>Edit Route: <?= esc($edit_route['number']) ?></h2>
  <form method="post">
    <input type="hidden" name="action" value="save_route">
    <input type="hidden" name="route_id" value="<?= esc($edit_route['id']) ?>">
    <div class="form-row">
      <div class="form-group"><label>Route Number</label><input type="text" name="number" value="<?= esc($edit_route['number']) ?>" required></div>
      <div class="form-group"><label>Color (hex)</label><input type="color" name="color" value="<?= esc($edit_route['color']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Name (Thai)</label><input type="text" name="name_th" value="<?= esc($edit_route['name']['th']) ?>"></div>
      <div class="form-group"><label>Name (English)</label><input type="text" name="name_en" value="<?= esc($edit_route['name']['en']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Description (Thai)</label><textarea name="desc_th" rows="2"><?= esc($edit_route['description']['th']) ?></textarea></div>
      <div class="form-group"><label>Description (English)</label><textarea name="desc_en" rows="2"><?= esc($edit_route['description']['en']) ?></textarea></div>
    </div>
    <div class="form-group">
      <label>Tracking iframe URL <small style="color:#aaa">— paste the embed URL from your GPS provider (per route)</small></label>
      <input type="url" name="tracking_url" value="<?= esc($edit_route['tracking_url'] ?? '') ?>" placeholder="https://trackback.gpsiam.net/embed/...">
    </div>
    <div class="form-group" style="display:flex;gap:24px;flex-wrap:wrap">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="active" value="1" <?= !empty($edit_route['active'])?'checked':'' ?>>
        Active (visible on website)
      </label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="circular" value="1" <?= !empty($edit_route['circular'])?'checked':'' ?>>
        🔄 Circular / Loop route (วนวงกลม) — no return direction
      </label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Save Route</button>
      <a href="routes.php" class="btn btn-sm" style="background:#f0f0f0;color:#333">Cancel</a>
    </div>
  </form>

  <!-- Stop order (drag to reorder) -->
  <?php if (!empty($edit_route['stops'])): ?>
  <div style="margin-top:28px;border-top:1px solid #e0e0e0;padding-top:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <h3 style="font-size:.95rem;font-weight:700;margin:0">Bus Stop Order <span style="color:#aaa;font-weight:400">(<?= count($edit_route['stops']) ?> stops)</span></h3>
      <span style="font-size:.78rem;color:#999">Drag ⠿ to reorder, then Save Route</span>
    </div>
    <input type="hidden" name="stop_order" id="stop-order" value="">
    <div id="stop-sortable" style="display:flex;flex-direction:column;gap:6px">
      <?php foreach ($edit_route['stops'] as $i => $s): ?>
      <div class="stop-sort-row" data-id="<?= esc($s['id']) ?>"
           style="display:flex;align-items:center;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;cursor:default">
        <span style="cursor:grab;color:#bbb;font-size:1.1rem;flex-shrink:0;user-select:none">⠿</span>
        <span style="background:<?= esc($edit_route['color']) ?>;color:#fff;border-radius:4px;padding:1px 8px;font-size:.75rem;font-weight:700;flex-shrink:0"><?= $i+1 ?></span>
        <span style="font-size:.88rem;font-weight:600;flex:1"><?= esc($s['name']['en']) ?></span>
        <span style="font-size:.8rem;color:#999"><?= esc($s['name']['th']) ?></span>
        <span style="font-size:.75rem;color:#bbb;font-family:monospace"><?= esc($s['id']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
  var el = document.getElementById('stop-sortable');
  if (!el) return;
  Sortable.create(el, {
    handle: 'span[style*="grab"]',
    animation: 150,
    onEnd: function() {
      var rows = el.querySelectorAll('.stop-sort-row');
      rows.forEach(function(r, i) {
        r.querySelector('span:nth-child(2)').textContent = i + 1;
      });
    }
  });
  // Collect order before any form in this card submits
  document.querySelectorAll('form').forEach(function(f) {
    f.addEventListener('submit', function() {
      var order = Array.from(el.querySelectorAll('.stop-sort-row')).map(function(r){ return r.dataset.id; });
      document.getElementById('stop-order').value = JSON.stringify(order);
    });
  });
})();
</script>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
