<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

$admin_page_title = 'Timetable Images';
$admin_active_nav = 'tt_images';

$tt_imgs = load_json('timetable_images.json');
$msg = $err = '';
$upload_dir = __DIR__ . '/../assets/images/timetable/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload') {
        $file   = $_FILES['image'] ?? null;
        $route  = $_POST['route'] ?? 'route1';
        $label_th = trim($_POST['label_th'] ?? '');
        $label_en = trim($_POST['label_en'] ?? '');

        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => 'File too large (exceeds server limit). Max ~20MB.',
            UPLOAD_ERR_FORM_SIZE  => 'File too large (exceeds form limit).',
            UPLOAD_ERR_PARTIAL    => 'File only partially uploaded. Try again.',
            UPLOAD_ERR_NO_FILE    => 'No file selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server missing temp folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server could not write file to disk.',
        ];
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $code = $file['error'] ?? -1;
            $err  = $upload_errors[$code] ?? 'Upload failed (error code ' . $code . ').';
        } elseif (!in_array(mime_content_type($file['tmp_name']), ['image/jpeg','image/png','image/webp','image/gif'])) {
            $err = 'Only image files allowed.';
        } else {
            $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fname = 'tt_' . date('Ymd_His') . '.' . strtolower($ext);
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $fname)) {
                $tt_imgs['images'][] = [
                    'id'       => 'tt_' . time(),
                    'route'    => $route,
                    'label'    => ['th' => $label_th ?: $label_en, 'en' => $label_en ?: $label_th],
                    'file'     => 'assets/images/timetable/' . $fname,
                    'active'   => true,
                    'uploaded' => date('Y-m-d'),
                ];
                save_json('timetable_images.json', $tt_imgs);
                $msg = 'Image uploaded successfully.';
            } else {
                $err = 'Could not move file.';
            }
        }

    } elseif ($action === 'toggle') {
        $id = $_POST['img_id'] ?? '';
        foreach ($tt_imgs['images'] as &$img) {
            if ($img['id'] === $id) { $img['active'] = !($img['active'] ?? true); break; }
        }
        unset($img);
        save_json('timetable_images.json', $tt_imgs);
        $msg = 'Status updated.';

    } elseif ($action === 'delete') {
        $id = $_POST['img_id'] ?? '';
        $tt_imgs['images'] = array_values(array_filter($tt_imgs['images'], fn($i) => $i['id'] !== $id));
        save_json('timetable_images.json', $tt_imgs);
        $msg = 'Image removed from list.';
    }
}

require_once __DIR__ . '/inc/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= esc($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= esc($err) ?></div><?php endif; ?>

<!-- Upload Form -->
<div class="admin-card">
  <h2>Upload New Timetable Image</h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="upload">
    <div class="form-row">
      <div class="form-group">
        <label>Image File (PNG/JPG)</label>
        <input type="file" name="image" accept="image/*" required>
      </div>
      <div class="form-group">
        <label>Route</label>
        <select name="route" class="form-group input">
          <?php foreach (load_json('routes.json') as $r): ?>
          <option value="<?= esc($r['id']) ?>"><?= esc($r['number'].' - '.$r['name']['en']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Label (Thai)</label>
        <input type="text" name="label_th" placeholder="ตารางเวลา สาย 1">
      </div>
      <div class="form-group">
        <label>Label (English)</label>
        <input type="text" name="label_en" placeholder="Route 1 Timetable">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">⬆ Upload</button>
    </div>
  </form>
</div>

<!-- Image list -->
<div class="admin-card">
  <h2>All Timetable Images</h2>
  <?php if (empty($tt_imgs['images'])): ?>
  <p style="color:#888">No images uploaded yet.</p>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:20px">
    <?php foreach ($tt_imgs['images'] as $img): ?>
    <div style="border:1px solid #e0e0e0;border-radius:8px;overflow:hidden">
      <img src="../<?= esc($img['file']) ?>" alt="" style="width:100%;height:160px;object-fit:cover">
      <div style="padding:12px">
        <p style="font-size:.85rem;font-weight:700"><?= esc($img['label']['en'] ?? '') ?></p>
        <p style="font-size:.78rem;color:#888"><?= esc($img['route']) ?> · <?= esc($img['uploaded']) ?></p>
        <div style="display:flex;gap:8px;margin-top:10px">
          <form method="post" style="display:inline">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="img_id" value="<?= esc($img['id']) ?>">
            <button type="submit" class="btn btn-sm" style="background:<?= $img['active']?'#27ae60':'#e74c3c' ?>;color:#fff;padding:5px 12px">
              <?= $img['active'] ? '✓ Active' : '✗ Hidden' ?>
            </button>
          </form>
          <form method="post" style="display:inline" onsubmit="return confirm('Remove this image entry?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="img_id" value="<?= esc($img['id']) ?>">
            <button type="submit" class="btn btn-sm" style="background:#f0f0f0;color:#e74c3c;padding:5px 12px">🗑</button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
