<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();

$admin_page_title = 'Attractions';
$admin_active_nav = 'attractions';

$routes  = array_values(array_filter(load_json('routes.json'), fn($r) => !empty($r['active'])));
$data    = load_json('attractions.json');
if (!isset($data['attractions'])) $data['attractions'] = [];

$msg   = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$edit_id = $_GET['id'] ?? '';

// ── DELETE ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $did = $_POST['delete_id'];
    $data['attractions'] = array_values(array_filter($data['attractions'], fn($a) => $a['id'] !== $did));
    save_json('attractions.json', $data);
    header('Location: attractions.php?msg=deleted'); exit;
}

// ── SAVE (add / edit) ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attraction'])) {

    $id       = trim($_POST['att_id'] ?? '');
    $is_new   = ($_POST['is_new'] ?? '0') === '1';
    $name_th  = trim($_POST['name_th']  ?? '');
    $name_en  = trim($_POST['name_en']  ?? '');
    $desc_th  = trim($_POST['desc_th']  ?? '');
    $desc_en  = trim($_POST['desc_en']  ?? '');
    $map_url  = trim($_POST['map_url']  ?? '');
    $active   = isset($_POST['active']) ? true : false;

    // Build nearby array from posted route/stop combos
    $nearby = [];
    foreach ($routes as $r) {
        $chk = $_POST['route_chk'][$r['id']] ?? '';
        $sid = $_POST['route_stop'][$r['id']] ?? '';
        if ($chk && $sid) {
            $nearby[] = ['route_id' => $r['id'], 'stop_id' => $sid];
        }
    }

    // Handle image upload
    $image = $_POST['existing_image'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $allowed)) {
            $error = 'Invalid image type. Allowed: jpg, png, webp, gif.';
        } else {
            $dir = __DIR__ . '/../assets/uploads/attractions/';
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $fname = 'att_' . preg_replace('/[^a-z0-9]/', '_', strtolower($id)) . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $fname)) {
                $image = 'assets/uploads/attractions/' . $fname;
            } else {
                $error = 'Image upload failed.';
            }
        }
    }

    if (!$error) {
        $att = [
            'id'          => $id,
            'name'        => ['th' => $name_th, 'en' => $name_en],
            'description' => ['th' => $desc_th,  'en' => $desc_en],
            'image'       => $image,
            'map_url'     => $map_url,
            'nearby'      => $nearby,
            'active'      => $active,
        ];

        if ($is_new) {
            $data['attractions'][] = $att;
        } else {
            foreach ($data['attractions'] as &$a) {
                if ($a['id'] === $id) { $a = $att; break; }
            }
        }
        save_json('attractions.json', $data);
        header('Location: attractions.php?msg=saved'); exit;
    }
}

// ── LOAD EDIT ITEM ──────────────────────────────────────────
$edit_item = null;
if ($action === 'edit' && $edit_id) {
    foreach ($data['attractions'] as $a) {
        if ($a['id'] === $edit_id) { $edit_item = $a; break; }
    }
}
if (isset($_GET['msg'])) $msg = $_GET['msg'] === 'saved' ? 'Saved successfully.' : 'Attraction deleted.';

// Build stop name lookup
$stop_opts = [];
foreach ($routes as $r) {
    foreach ($r['stops'] as $s) {
        $stop_opts[$r['id']][$s['id']] = $s['name']['th'] . ' / ' . $s['name']['en'];
    }
}
?>
<?php require_once __DIR__ . '/inc/admin_header.php'; ?>

<div class="admin-card">
<?php if ($action === 'list'): ?>

  <!-- ── LIST VIEW ── -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <h2>Attractions</h2>
    <a href="?action=add" class="btn btn-primary btn-sm">+ Add Attraction</a>
  </div>

  <?php if ($msg): ?><div class="alert-success"><?= esc($msg) ?></div><?php endif; ?>

  <?php if ($data['attractions']): ?>
  <table class="admin-table">
    <thead><tr>
      <th>Image</th><th>Name</th><th>Routes</th><th>Active</th><th>Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($data['attractions'] as $att): ?>
    <tr>
      <td>
        <?php if (!empty($att['image'])): ?>
        <img src="../<?= esc($att['image']) ?>" style="width:60px;height:40px;object-fit:cover;border-radius:4px">
        <?php else: ?><span style="font-size:1.4rem">🏖️</span><?php endif; ?>
      </td>
      <td>
        <strong><?= esc($att['name']['th'] ?? '') ?></strong><br>
        <small style="color:#888"><?= esc($att['name']['en'] ?? '') ?></small>
      </td>
      <td>
        <?php foreach ($att['nearby'] ?? [] as $nb):
          $r = null;
          foreach ($routes as $rr) { if ($rr['id']===$nb['route_id']) { $r=$rr; break; } }
          if (!$r) continue;
        ?>
        <span style="display:inline-block;background:<?= esc($r['color']) ?>;color:#fff;border-radius:4px;padding:2px 7px;font-size:.75rem;font-weight:700;margin:1px"><?= esc($r['number']) ?></span>
        <?php endforeach; ?>
      </td>
      <td><?= !empty($att['active'])?'✅':'❌' ?></td>
      <td>
        <a href="?action=edit&id=<?= esc($att['id']) ?>" class="btn btn-sm">Edit</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this attraction?')">
          <input type="hidden" name="delete_id" value="<?= esc($att['id']) ?>">
          <button class="btn btn-sm btn-danger">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="color:#888">No attractions yet. <a href="?action=add">Add one</a>.</p>
  <?php endif; ?>

<?php else: ?>

  <!-- ── ADD / EDIT FORM ── -->
  <?php
    $is_new  = ($action === 'add');
    $item    = $edit_item ?? [];
    $i_id    = $item['id']   ?? '';
    $i_nth   = $item['name']['th'] ?? '';
    $i_nen   = $item['name']['en'] ?? '';
    $i_dth   = $item['description']['th'] ?? '';
    $i_den   = $item['description']['en'] ?? '';
    $i_map   = $item['map_url'] ?? '';
    $i_img   = $item['image']   ?? '';
    $i_act   = $item['active']  ?? true;
    // build nearby lookup for this item
    $nb_map  = [];
    foreach ($item['nearby'] ?? [] as $nb) $nb_map[$nb['route_id']] = $nb['stop_id'];
  ?>

  <h2><?= $is_new ? 'Add Attraction' : 'Edit Attraction' ?></h2>
  <?php if ($error): ?><div class="alert-error"><?= esc($error) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="save_attraction" value="1">
    <input type="hidden" name="is_new" value="<?= $is_new?'1':'0' ?>">
    <input type="hidden" name="existing_image" value="<?= esc($i_img) ?>">

    <div class="form-group">
      <label>ID (slug, no spaces) <?= $is_new?'*':'' ?></label>
      <input type="text" name="att_id" value="<?= esc($i_id) ?>"
             placeholder="e.g. surin_beach" <?= !$is_new?'readonly':'' ?> required>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Name (Thai)</label>
        <input type="text" name="name_th" value="<?= esc($i_nth) ?>" required>
      </div>
      <div class="form-group">
        <label>Name (English)</label>
        <input type="text" name="name_en" value="<?= esc($i_nen) ?>" required>
      </div>
    </div>

    <div class="form-group">
      <label>Description (Thai)</label>
      <textarea name="desc_th" rows="3"><?= esc($i_dth) ?></textarea>
    </div>
    <div class="form-group">
      <label>Description (English)</label>
      <textarea name="desc_en" rows="3"><?= esc($i_den) ?></textarea>
    </div>

    <div class="form-group">
      <label>Google Maps URL</label>
      <input type="url" name="map_url" value="<?= esc($i_map) ?>" placeholder="https://maps.google.com/?q=...">
    </div>

    <div class="form-group">
      <label>Image</label>
      <?php if ($i_img): ?>
      <div style="margin-bottom:8px">
        <img src="../<?= esc($i_img) ?>" style="height:80px;border-radius:6px;object-fit:cover">
        <small style="display:block;color:#888;margin-top:4px">Upload new image to replace</small>
      </div>
      <?php endif; ?>
      <input type="file" name="image" accept="image/*">
    </div>

    <!-- Nearby routes / stops -->
    <div class="form-group">
      <label>Routes &amp; Stops Nearby</label>
      <div style="display:flex;flex-direction:column;gap:10px;margin-top:6px">
        <?php foreach ($routes as $r):
          $checked = isset($nb_map[$r['id']]);
          $sel_stop = $nb_map[$r['id']] ?? '';
        ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid var(--border,#e0e0e0);border-radius:8px">
          <input type="checkbox" name="route_chk[<?= esc($r['id']) ?>]" value="1"
                 id="chk_<?= esc($r['id']) ?>" <?= $checked?'checked':'' ?>
                 onchange="document.getElementById('stop_<?= esc($r['id']) ?>').disabled=!this.checked">
          <span style="background:<?= esc($r['color']) ?>;color:#fff;border-radius:5px;padding:2px 10px;font-weight:700;font-size:.85rem">
            <?= esc($r['number']) ?>
          </span>
          <span style="font-weight:600;font-size:.88rem"><?= esc($r['name']['en']) ?></span>
          <select name="route_stop[<?= esc($r['id']) ?>]" id="stop_<?= esc($r['id']) ?>"
                  <?= !$checked?'disabled':'' ?> style="margin-left:auto;font-size:.85rem;padding:4px 8px;border-radius:6px;border:1.5px solid #ddd">
            <option value="">— select stop —</option>
            <?php foreach ($stop_opts[$r['id']] ?? [] as $sid => $sname): ?>
            <option value="<?= esc($sid) ?>" <?= $sel_stop===$sid?'selected':'' ?>><?= esc($sname) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-group">
      <label><input type="checkbox" name="active" value="1" <?= $i_act?'checked':'' ?>> Active (visible on site)</label>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save Attraction</button>
      <a href="attractions.php" class="btn">Cancel</a>
    </div>
  </form>

<?php endif; ?>
</div>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
