<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();

$admin_page_title = 'Manage Bus Stops';
$admin_active_nav = 'stops';

$routes     = load_json('routes.json');
$stops_data = load_json('stops.json');
if (!isset($stops_data['stops'])) $stops_data['stops'] = [];

$msg = $err = '';
$action  = $_GET['action'] ?? 'list';
$edit_id = $_GET['id']     ?? '';

// ── Helper: save routes.json ──────────────────────────────────
function save_routes($routes) { save_json('routes.json', $routes); }

// ── ADD STOP ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stop'])) {
    $rid_list = $_POST['route_ids'] ?? [];   // now multi-select
    $sid      = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($_POST['stop_id'] ?? '')));
    $nth      = trim($_POST['name_th'] ?? '');
    $nen      = trim($_POST['name_en'] ?? '');
    $lat      = (float)($_POST['lat'] ?? 0);
    $lng      = (float)($_POST['lng'] ?? 0);

    if (!$sid)              $err = 'Stop ID is required.';
    elseif (!$rid_list)     $err = 'Select at least one route.';
    elseif (!$nth || !$nen) $err = 'Both Thai and English names are required.';
    else {
        // Check ID unique across all routes
        foreach ($routes as $r) {
            foreach ($r['stops'] as $s) {
                if ($s['id'] === $sid) { $err = "Stop ID \"$sid\" already exists."; break 2; }
            }
        }
    }
    if (!$err) {
        $new = ['id'=>$sid,'name'=>['th'=>$nth,'en'=>$nen],'lat'=>$lat,'lng'=>$lng];
        foreach ($routes as &$r) {
            if (!in_array($r['id'], $rid_list)) continue;
            $r['stops'][] = $new;
        }
        unset($r);
        save_routes($routes);
        header('Location: stops.php?msg=added'); exit;
    }
}

// ── SAVE STOP DETAILS (name / lat / lng / routes) ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_details'])) {
    $sid  = $_POST['stop_id'] ?? '';
    $rids = $_POST['route_ids'] ?? [];   // selected route IDs

    $new_stop = [
        'id'   => $sid,
        'name' => ['th' => trim($_POST['name_th'] ?? ''), 'en' => trim($_POST['name_en'] ?? '')],
        'lat'  => (float)($_POST['lat'] ?? 0),
        'lng'  => (float)($_POST['lng'] ?? 0),
    ];

    foreach ($routes as &$r) {
        $idx = null;
        foreach ($r['stops'] as $i => $s) {
            if ($s['id'] === $sid) { $idx = $i; break; }
        }
        $selected = in_array($r['id'], $rids);
        if ($selected && $idx !== null) {
            // Update existing entry
            $r['stops'][$idx] = $new_stop;
        } elseif ($selected && $idx === null) {
            // Add to this route
            $r['stops'][] = $new_stop;
        } elseif (!$selected && $idx !== null) {
            // Remove from this route
            $r['stops'] = array_values(array_filter($r['stops'], fn($s) => $s['id'] !== $sid));
        }
    }
    unset($r);
    save_routes($routes);
    header('Location: stops.php?msg=saved&action=edit&id='.urlencode($sid)); exit;
}

// ── SAVE STOP CONTENT (description / image) ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_content'])) {
    $sid = $_POST['stop_id'] ?? '';
    if (!$sid) { $err = 'Invalid stop.'; }
    else {
        $entry = $stops_data['stops'][$sid] ?? [];
        $entry['description']['th'] = trim($_POST['desc_th'] ?? '');
        $entry['description']['en'] = trim($_POST['desc_en'] ?? '');

        if (!empty($_FILES['image']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $dir = __DIR__ . '/../assets/uploads/stops/';
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $fname = 'stop_' . preg_replace('/[^a-z0-9_]/', '', $sid) . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $fname)) {
                    $entry['image'] = 'assets/uploads/stops/' . $fname;
                }
            } else {
                $err = 'Image must be JPG, PNG, or WebP.';
            }
        }
        if (!empty($_POST['remove_image'])) $entry['image'] = '';

        if (!$err) {
            $stops_data['stops'][$sid] = $entry;
            save_json('stops.json', $stops_data);
            header('Location: stops.php?msg=saved&action=edit&id='.urlencode($sid)); exit;
        }
    }
}

// ── DELETE STOP ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_stop'])) {
    $sid = $_POST['stop_id'] ?? '';
    foreach ($routes as &$r) {
        $r['stops'] = array_values(array_filter($r['stops'], fn($s) => $s['id'] !== $sid));
    }
    unset($r);
    save_routes($routes);
    header('Location: stops.php?msg=deleted'); exit;
}

if (isset($_GET['msg'])) {
    $msg = match($_GET['msg']) { 'saved'=>'Saved.', 'added'=>'Stop added.', 'deleted'=>'Stop deleted.', default=>'' };
}

// ── Build deduplicated stop map for list view ─────────────────
// stop_id => ['stop' => $s, 'routes' => [$r, ...]]
$stop_map = [];
foreach ($routes as $r) {
    foreach ($r['stops'] as $s) {
        if (!isset($stop_map[$s['id']])) {
            $stop_map[$s['id']] = ['stop' => $s, 'routes' => []];
        }
        $stop_map[$s['id']]['routes'][] = $r;
    }
}

// ── For edit view — find the stop and its current route IDs ───
$edit_stop      = null;
$stop_route_ids = [];
if ($action === 'edit' && $edit_id) {
    foreach ($routes as $r) {
        foreach ($r['stops'] as $s) {
            if ($s['id'] === $edit_id) {
                if (!$edit_stop) $edit_stop = $s;
                $stop_route_ids[] = $r['id'];
            }
        }
    }
}
$edit_extra = $edit_id ? ($stops_data['stops'][$edit_id] ?? []) : [];
?>
<?php require_once __DIR__ . '/inc/admin_header.php'; ?>

<?php if ($msg): ?><div class="alert alert-success"><?= esc($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= esc($err) ?></div><?php endif; ?>

<?php if ($action === 'edit' && $edit_stop): ?>
<!-- ══ EDIT STOP ══════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
  <a href="stops.php" class="btn btn-sm">← All Stops</a>
  <span style="font-size:.85rem;color:#888">Editing: <strong><?= esc($edit_stop['name']['en']) ?></strong></span>
</div>

<!-- Stop details -->
<div class="admin-card">
  <h2>Stop Details</h2>
  <form method="post">
    <input type="hidden" name="save_details" value="1">
    <input type="hidden" name="stop_id" value="<?= esc($edit_id) ?>">

    <div class="form-row">
      <div class="form-group">
        <label>Stop ID <small style="color:#aaa">(cannot change)</small></label>
        <input type="text" value="<?= esc($edit_id) ?>" disabled style="background:#f5f5f5;color:#888">
      </div>
    </div>

    <!-- Routes: multi-checkbox -->
    <div class="form-group">
      <label>Routes <small style="color:#aaa">— select all routes that serve this stop</small></label>
      <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px">
        <?php foreach ($routes as $r): ?>
        <label style="display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border:1.5px solid <?= in_array($r['id'],$stop_route_ids)?esc($r['color']):'#e0e0e0' ?>;border-radius:8px;cursor:pointer;transition:border-color .15s">
          <input type="checkbox" name="route_ids[]" value="<?= esc($r['id']) ?>"
                 <?= in_array($r['id'], $stop_route_ids) ? 'checked' : '' ?>>
          <span style="background:<?= esc($r['color']) ?>;color:#fff;border-radius:5px;padding:2px 10px;font-weight:800;font-size:.85rem"><?= esc($r['number']) ?></span>
          <span style="font-size:.88rem"><?= esc($r['name']['en']) ?></span>
        </label>
        <?php endforeach; ?>
      </div>
      <small style="color:#aaa;display:block;margin-top:6px">⚠️ Unchecking a route will remove this stop from that route.</small>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Name (Thai) *</label>
        <input type="text" name="name_th" value="<?= esc($edit_stop['name']['th'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Name (English) *</label>
        <input type="text" name="name_en" value="<?= esc($edit_stop['name']['en'] ?? '') ?>" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Latitude</label>
        <input type="number" name="lat" step="0.0001" value="<?= esc($edit_stop['lat'] ?? '') ?>" placeholder="e.g. 7.9519">
      </div>
      <div class="form-group">
        <label>Longitude</label>
        <input type="number" name="lng" step="0.0001" value="<?= esc($edit_stop['lng'] ?? '') ?>" placeholder="e.g. 98.2977">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Save Details</button>
    </div>
  </form>
  <form method="post" style="display:inline;margin-top:8px" onsubmit="return confirm('Delete this stop from ALL routes?')">
    <input type="hidden" name="delete_stop" value="1">
    <input type="hidden" name="stop_id" value="<?= esc($edit_id) ?>">
    <button class="btn btn-danger btn-sm">🗑 Delete Stop</button>
  </form>
</div>

<!-- Stop content -->
<div class="admin-card">
  <h2>Stop Content <small style="font-size:.8rem;font-weight:400;color:#888">— shown on public stop page</small></h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="save_content" value="1">
    <input type="hidden" name="stop_id" value="<?= esc($edit_id) ?>">

    <?php if (!empty($edit_extra['image'])): ?>
    <div style="margin-bottom:16px">
      <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.85rem">Current Image</label>
      <div style="display:flex;align-items:flex-start;gap:16px">
        <img src="<?= esc('../' . $edit_extra['image']) ?>" style="width:180px;height:110px;object-fit:cover;border-radius:8px;border:1px solid #e0e0e0">
        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;cursor:pointer;font-size:.88rem;color:#e74c3c">
          <input type="checkbox" name="remove_image" value="1"> Remove image
        </label>
      </div>
    </div>
    <?php endif; ?>

    <div class="form-group" style="margin-bottom:16px">
      <label><?= empty($edit_extra['image']) ? 'Upload Image' : 'Replace Image' ?></label>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
             style="border:1px solid #ddd;border-radius:6px;padding:6px;width:100%">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Description (Thai)</label>
        <textarea name="desc_th" rows="4" placeholder="คำอธิบายป้ายนี้..."><?= esc($edit_extra['description']['th'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label>Description (English)</label>
        <textarea name="desc_en" rows="4" placeholder="Description of this stop..."><?= esc($edit_extra['description']['en'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Save Content</button>
    </div>
  </form>
</div>

<?php elseif ($action === 'add'): ?>
<!-- ══ ADD STOP ═══════════════════════════════════════════════ -->
<div class="admin-card">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
    <a href="stops.php" class="btn btn-sm">← Back</a>
    <h2 style="margin:0">Add New Bus Stop</h2>
  </div>
  <form method="post">
    <input type="hidden" name="add_stop" value="1">

    <!-- Routes: multi-checkbox -->
    <div class="form-group">
      <label>Add to Route(s) * <small style="color:#aaa">— select all routes that serve this stop</small></label>
      <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px">
        <?php foreach ($routes as $r): ?>
        <label style="display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border:1.5px solid #e0e0e0;border-radius:8px;cursor:pointer">
          <input type="checkbox" name="route_ids[]" value="<?= esc($r['id']) ?>">
          <span style="background:<?= esc($r['color']) ?>;color:#fff;border-radius:5px;padding:2px 10px;font-weight:800;font-size:.85rem"><?= esc($r['number']) ?></span>
          <span style="font-size:.88rem"><?= esc($r['name']['en']) ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Stop ID * <small style="color:#aaa">lowercase, no spaces (e.g. airport)</small></label>
        <input type="text" name="stop_id" placeholder="e.g. airport" required pattern="[a-z0-9_]+">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Name (Thai) *</label>
        <input type="text" name="name_th" placeholder="สนามบินภูเก็ต" required>
      </div>
      <div class="form-group">
        <label>Name (English) *</label>
        <input type="text" name="name_en" placeholder="Phuket Airport" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Latitude</label>
        <input type="number" name="lat" step="0.0001" placeholder="7.9519">
      </div>
      <div class="form-group">
        <label>Longitude</label>
        <input type="number" name="lng" step="0.0001" placeholder="98.2977">
      </div>
    </div>
    <p style="font-size:.82rem;color:#888;margin-bottom:16px">
      💡 The stop will be added at the end of selected route(s). Go to <a href="routes.php">Routes → Edit</a> to reorder stops.
    </p>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">➕ Add Stop</button>
      <a href="stops.php" class="btn">Cancel</a>
    </div>
  </form>
</div>

<?php else: ?>
<!-- ══ LIST ═══════════════════════════════════════════════════ -->
<div class="admin-card">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
      <h2 style="margin:0">All Bus Stops</h2>
      <p style="color:#888;font-size:.85rem;margin-top:2px"><?= count($stop_map) ?> stops across <?= count($routes) ?> routes</p>
    </div>
    <a href="?action=add" class="btn btn-primary btn-sm">+ Add Stop</a>
  </div>
  <div style="overflow-x:auto">
    <table class="admin-table">
      <thead><tr>
        <th>Stop Name</th><th>Routes</th><th>Lat / Lng</th><th>Description</th><th>Image</th><th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($stop_map as $sid => $item):
        $s     = $item['stop'];
        $rlist = $item['routes'];
        $extra = $stops_data['stops'][$sid] ?? [];
        $desc  = $extra['description']['en'] ?? '';
      ?>
      <tr>
        <td>
          <strong><?= esc($s['name']['en']) ?></strong><br>
          <small style="color:#888"><?= esc($s['name']['th']) ?></small><br>
          <small style="color:#bbb;font-family:monospace"><?= esc($sid) ?></small>
        </td>
        <td>
          <?php foreach ($rlist as $r): ?>
          <span style="display:inline-block;background:<?= esc($r['color']) ?>;color:#fff;border-radius:4px;padding:2px 8px;font-size:.78rem;font-weight:700;margin:1px"><?= esc($r['number']) ?></span>
          <?php endforeach; ?>
        </td>
        <td style="font-size:.8rem;color:#888;font-family:monospace">
          <?= $s['lat'] ? esc($s['lat'].', '.$s['lng']) : '<span style="color:#ddd">—</span>' ?>
        </td>
        <td style="font-size:.8rem;color:#666;max-width:180px">
          <?= $desc ? esc(mb_substr($desc,0,60)).(mb_strlen($desc)>60?'…':'') : '<span style="color:#ddd">—</span>' ?>
        </td>
        <td style="text-align:center"><?= !empty($extra['image']) ? '✅' : '<span style="color:#ddd">—</span>' ?></td>
        <td style="white-space:nowrap">
          <a href="?action=edit&id=<?= urlencode($sid) ?>" class="btn btn-sm">Edit</a>
          <a href="../stop.php?id=<?= urlencode($sid) ?>" target="_blank" class="btn btn-sm">↗</a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
