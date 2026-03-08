<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();

$admin_page_title = 'Manage Passes';
$admin_active_nav = 'passes';

$all_routes = array_values(array_filter(load_json('routes.json'), fn($r) => !empty($r['active'])));
$data = load_json('passes.json');
if (!isset($data['passes'])) $data['passes'] = [];

$msg = $err = '';
$action   = $_GET['action'] ?? 'list';
$edit_id  = $_GET['id'] ?? '';

// ── DELETE ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $data['passes'] = array_values(array_filter($data['passes'], fn($p) => $p['id'] !== $_POST['delete_id']));
    save_json('passes.json', $data);
    header('Location: passes.php?msg=deleted'); exit;
}

// ── SAVE NOTE ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {
    $data['note']['th'] = trim($_POST['note_th'] ?? '');
    $data['note']['en'] = trim($_POST['note_en'] ?? '');
    save_json('passes.json', $data);
    header('Location: passes.php?msg=saved'); exit;
}

// ── SAVE PASS (add / edit) ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_pass'])) {
    $id      = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($_POST['pass_id'] ?? '')));
    $is_new  = ($_POST['is_new'] ?? '0') === '1';
    $name_th = trim($_POST['name_th'] ?? '');
    $name_en = trim($_POST['name_en'] ?? '');
    $v_th    = trim($_POST['validity_th'] ?? '');
    $v_en    = trim($_POST['validity_en'] ?? '');
    $price   = max(0, (int)($_POST['price'] ?? 0));
    $color   = $_POST['color'] ?? '#4a90d9';
    $buy_url = trim($_POST['buy_url'] ?? '');
    $routes  = $_POST['routes'] ?? [];   // array of route IDs, empty = all

    if (!$id)      { $err = 'ID is required.'; }
    elseif ($is_new && in_array($id, array_column($data['passes'], 'id'))) {
        $err = 'A pass with this ID already exists.';
    }

    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) $color = '#4a90d9';

    if (!$err) {
        // Keep existing image by default
        $existing_image = trim($_POST['existing_image'] ?? '');
        $image = $existing_image;

        // Handle new image upload
        if (!empty($_FILES['pass_image']['name'])) {
            $upload_dir = __DIR__ . '/../assets/uploads/passes/';
            $ext  = strtolower(pathinfo($_FILES['pass_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (in_array($ext, $allowed) && $_FILES['pass_image']['size'] < 5*1024*1024) {
                $fname = 'pass_' . $id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['pass_image']['tmp_name'], $upload_dir . $fname)) {
                    $image = 'assets/uploads/passes/' . $fname;
                }
            }
        }

        // Remove image if requested
        if (!empty($_POST['remove_image'])) $image = '';

        $pass = [
            'id'       => $id,
            'name'     => ['th' => $name_th, 'en' => $name_en],
            'price'    => $price,
            'validity' => ['th' => $v_th, 'en' => $v_en],
            'color'    => $color,
            'routes'   => array_values($routes),
            'buy_url'  => $buy_url,
            'image'    => $image,
        ];
        if ($is_new) {
            $data['passes'][] = $pass;
        } else {
            foreach ($data['passes'] as &$p) {
                if ($p['id'] === $id) { $p = $pass; break; }
            }
            unset($p);
        }
        save_json('passes.json', $data);
        header('Location: passes.php?msg=saved'); exit;
    }
}

// ── LOAD EDIT ITEM ──────────────────────────────────────────
$edit_item = null;
if ($action === 'edit' && $edit_id) {
    foreach ($data['passes'] as $p) {
        if ($p['id'] === $edit_id) { $edit_item = $p; break; }
    }
}
if (isset($_GET['msg'])) $msg = $_GET['msg'] === 'saved' ? 'Saved successfully.' : 'Pass deleted.';
?>
<?php require_once __DIR__ . '/inc/admin_header.php'; ?>

<?php if ($msg): ?><div class="alert alert-success"><?= esc($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= esc($err) ?></div><?php endif; ?>

<?php if ($action === 'list'): ?>

<!-- ── LIST ── -->
<div class="admin-card">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <h2>Pass Types</h2>
    <a href="?action=add" class="btn btn-primary btn-sm">+ Add Pass</a>
  </div>
  <div style="overflow-x:auto">
    <table class="admin-table">
      <thead><tr>
        <th>Pass</th><th>Price</th><th>Routes</th><th>Validity (EN)</th><th>Buy URL</th><th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($data['passes'] as $p): ?>
      <tr>
        <td>
          <span style="display:inline-flex;align-items:center;gap:8px">
            <?php if (!empty($p['image'])): ?>
            <img src="<?= esc('../' . $p['image']) ?>" style="width:36px;height:36px;object-fit:cover;border-radius:5px;flex-shrink:0">
            <?php else: ?>
            <span style="width:14px;height:14px;border-radius:3px;background:<?= esc($p['color']) ?>;flex-shrink:0;display:inline-block"></span>
            <?php endif; ?>
            <div>
              <strong><?= esc($p['name']['en'] ?? '') ?></strong><br>
              <small style="color:#888"><?= esc($p['name']['th'] ?? '') ?></small>
            </div>
          </span>
        </td>
        <td><strong style="color:<?= esc($p['color']) ?>">฿<?= number_format($p['price']) ?></strong></td>
        <td>
          <?php
          $pr = $p['routes'] ?? [];
          if (empty($pr)): ?>
            <span style="font-size:.8rem;color:#27ae60;font-weight:600">All Routes</span>
          <?php else:
            foreach ($pr as $rid):
              $r = null;
              foreach ($all_routes as $rr) { if ($rr['id']===$rid) { $r=$rr; break; } }
              if (!$r) continue;
          ?>
            <span style="display:inline-block;background:<?= esc($r['color']) ?>;color:#fff;border-radius:4px;padding:1px 7px;font-size:.75rem;font-weight:700;margin:1px"><?= esc($r['number']) ?></span>
          <?php endforeach; endif; ?>
        </td>
        <td style="font-size:.85rem"><?= esc($p['validity']['en'] ?? '') ?></td>
        <td style="font-size:.78rem;color:#888"><?= $p['buy_url'] ? '✅' : '—' ?></td>
        <td>
          <a href="?action=edit&id=<?= esc($p['id']) ?>" class="btn btn-sm">Edit</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete this pass?')">
            <input type="hidden" name="delete_id" value="<?= esc($p['id']) ?>">
            <button class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── NOTE ── -->
<div class="admin-card">
  <h2>Pass Usage Note</h2>
  <p style="color:#888;font-size:.88rem;margin-bottom:16px">Shown at the top of the Passes page.</p>
  <form method="post">
    <input type="hidden" name="save_note" value="1">
    <div class="form-row">
      <div class="form-group">
        <label>Note (Thai)</label>
        <textarea name="note_th" rows="3"><?= esc($data['note']['th'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label>Note (English)</label>
        <textarea name="note_en" rows="3"><?= esc($data['note']['en'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Save Note</button>
    </div>
  </form>
</div>

<?php else: ?>

<!-- ── ADD / EDIT FORM ── -->
<?php
  $is_new = ($action === 'add');
  $item   = $edit_item ?? [];
  $i_id   = $item['id']            ?? '';
  $i_nth  = $item['name']['th']    ?? '';
  $i_nen  = $item['name']['en']    ?? '';
  $i_vth  = $item['validity']['th'] ?? '';
  $i_ven  = $item['validity']['en'] ?? '';
  $i_pr   = $item['price']         ?? 0;
  $i_col  = $item['color']         ?? '#4a90d9';
  $i_url  = $item['buy_url']       ?? '';
  $i_rts  = $item['routes']        ?? [];  // [] = all
  $i_img  = $item['image']         ?? '';
?>
<div class="admin-card">
  <h2><?= $is_new ? 'Add New Pass' : 'Edit Pass' ?></h2>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="save_pass" value="1">
    <input type="hidden" name="is_new" value="<?= $is_new?'1':'0' ?>">

    <div class="form-row">
      <div class="form-group">
        <label>ID (slug) <?= $is_new?'*':'' ?></label>
        <input type="text" name="pass_id" value="<?= esc($i_id) ?>"
               placeholder="e.g. 1day, 7day" <?= !$is_new?'readonly':'' ?> required>
      </div>
      <div class="form-group">
        <label>Card Color</label>
        <input type="color" name="color" value="<?= esc($i_col) ?>"
               style="width:60px;height:38px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Name (Thai)</label>
        <input type="text" name="name_th" value="<?= esc($i_nth) ?>" placeholder="7 วัน" required>
      </div>
      <div class="form-group">
        <label>Name (English)</label>
        <input type="text" name="name_en" value="<?= esc($i_nen) ?>" placeholder="7 DAYS PASS" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Price (฿)</label>
        <input type="number" name="price" value="<?= esc($i_pr) ?>" min="0" required>
      </div>
      <div class="form-group">
        <label>Buy URL</label>
        <input type="url" name="buy_url" value="<?= esc($i_url) ?>" placeholder="https://...">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Validity Text (Thai)</label>
        <input type="text" name="validity_th" value="<?= esc($i_vth) ?>" placeholder="ใช้ได้ 168 ชั่วโมง">
      </div>
      <div class="form-group">
        <label>Validity Text (English)</label>
        <input type="text" name="validity_en" value="<?= esc($i_ven) ?>" placeholder="Valid 168 hours">
      </div>
    </div>

    <!-- Routes -->
    <div class="form-group">
      <label>Valid Routes</label>
      <div style="display:flex;flex-direction:column;gap:8px;margin-top:6px">
        <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid var(--border,#e0e0e0);border-radius:8px;cursor:pointer">
          <input type="radio" name="route_mode" value="all"
                 <?= empty($i_rts)?'checked':'' ?>
                 onchange="toggleRouteBoxes(this.value)">
          <span style="font-weight:600">✅ All Routes</span>
          <small style="color:#888">Pass is valid on every route</small>
        </label>
        <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid var(--border,#e0e0e0);border-radius:8px;cursor:pointer">
          <input type="radio" name="route_mode" value="specific"
                 <?= !empty($i_rts)?'checked':'' ?>
                 onchange="toggleRouteBoxes(this.value)">
          <span style="font-weight:600">🔀 Specific Routes</span>
          <small style="color:#888">Choose which routes below</small>
        </label>
        <div id="route-boxes" style="display:<?= !empty($i_rts)?'flex':'none' ?>;flex-wrap:wrap;gap:10px;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;margin-top:2px">
          <?php foreach ($all_routes as $r): ?>
          <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer">
            <input type="checkbox" name="routes[]" value="<?= esc($r['id']) ?>"
                   <?= in_array($r['id'], $i_rts)?'checked':'' ?>>
            <span style="background:<?= esc($r['color']) ?>;color:#fff;border-radius:5px;padding:3px 10px;font-weight:700;font-size:.85rem">
              <?= esc($r['number']) ?>
            </span>
            <span style="font-size:.88rem"><?= esc($r['name']['en']) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Pass Image -->
    <div class="form-group">
      <label>Pass Image <small style="color:#aaa">— shown on the pass card (optional)</small></label>
      <input type="hidden" name="existing_image" value="<?= esc($i_img) ?>">
      <?php if ($i_img): ?>
      <div style="margin-bottom:10px;display:flex;align-items:center;gap:12px">
        <img src="<?= esc('../' . $i_img) ?>" alt="" style="height:80px;border-radius:8px;object-fit:cover;border:1px solid #eee">
        <label style="display:flex;align-items:center;gap:6px;font-size:.85rem;cursor:pointer;color:#e74c3c">
          <input type="checkbox" name="remove_image" value="1"> Remove image
        </label>
      </div>
      <?php endif; ?>
      <input type="file" name="pass_image" accept="image/*" style="margin-top:4px">
      <small style="color:#aaa;display:block;margin-top:4px">JPG, PNG, WebP — max 5MB</small>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Save Pass</button>
      <a href="passes.php" class="btn">Cancel</a>
    </div>
  </form>
</div>

<script>
function toggleRouteBoxes(v) {
  document.getElementById('route-boxes').style.display = v === 'specific' ? 'flex' : 'none';
  if (v === 'all') {
    document.querySelectorAll('#route-boxes input[type=checkbox]').forEach(c => c.checked = false);
  }
}
</script>

<?php endif; ?>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
