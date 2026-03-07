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
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="active" value="1" <?= !empty($edit_route['active'])?'checked':'' ?>>
        Active (visible on website)
      </label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Save Route</button>
      <a href="routes.php" class="btn btn-sm" style="background:#f0f0f0;color:#333">Cancel</a>
    </div>
  </form>

  <!-- Stop list (read-only for now) -->
  <?php if (!empty($edit_route['stops'])): ?>
  <div style="margin-top:20px">
    <h3 style="font-size:.95rem;color:#888;margin-bottom:10px">Bus Stops (<?= count($edit_route['stops']) ?>)</h3>
    <div style="display:flex;flex-wrap:wrap;gap:6px">
      <?php foreach ($edit_route['stops'] as $i => $s): ?>
      <span style="background:#f0f0f0;border-radius:20px;padding:3px 12px;font-size:.8rem">
        <?= $i+1 ?>. <?= esc($s['name']['en']) ?>
      </span>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
