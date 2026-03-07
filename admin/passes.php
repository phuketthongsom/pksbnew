<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

$admin_page_title = 'Manage Passes';
$admin_active_nav = 'passes';

$passes = load_json('passes.json');
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_all') {
        $prices      = $_POST['price']       ?? [];
        $validity_th = $_POST['validity_th'] ?? [];
        $validity_en = $_POST['validity_en'] ?? [];
        $buy_urls    = $_POST['buy_url']     ?? [];
        $colors      = $_POST['color']       ?? [];

        foreach ($passes['passes'] as &$p) {
            $id = $p['id'];
            if (isset($prices[$id]))      $p['price']          = max(0, (int)$prices[$id]);
            if (isset($validity_th[$id])) $p['validity']['th'] = trim($validity_th[$id]);
            if (isset($validity_en[$id])) $p['validity']['en'] = trim($validity_en[$id]);
            if (isset($buy_urls[$id]))    $p['buy_url']        = trim($buy_urls[$id]);
            if (isset($colors[$id]) && preg_match('/^#[0-9a-fA-F]{6}$/', $colors[$id])) {
                $p['color'] = $colors[$id];
            }
        }
        unset($p);

        $passes['note']['th'] = trim($_POST['note_th'] ?? '');
        $passes['note']['en'] = trim($_POST['note_en'] ?? '');

        save_json('passes.json', $passes);
        $msg = 'All passes saved successfully.';
    }
}

require_once __DIR__ . '/inc/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= esc($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= esc($err) ?></div><?php endif; ?>

<form method="post">
  <input type="hidden" name="action" value="save_all">

  <div class="admin-card">
    <h2>Pass Prices &amp; Details</h2>
    <p style="color:#888;font-size:.88rem;margin-bottom:20px">Edit all passes below and click <strong>Save All</strong> at the bottom.</p>

    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:120px">Pass</th>
            <th>Price (฿)</th>
            <th>Card Color</th>
            <th>Validity (Thai)</th>
            <th>Validity (English)</th>
            <th>Buy URL</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($passes['passes'] as $p): ?>
          <tr>
            <td>
              <span style="display:inline-flex;align-items:center;gap:8px">
                <span style="width:14px;height:14px;border-radius:3px;background:<?= esc($p['color']) ?>;display:inline-block"></span>
                <strong><?= esc($p['name']['en']) ?></strong>
              </span>
            </td>
            <td>
              <input type="number" name="price[<?= esc($p['id']) ?>]"
                     value="<?= esc($p['price']) ?>" min="0" required
                     style="width:90px">
            </td>
            <td>
              <input type="color" name="color[<?= esc($p['id']) ?>]"
                     value="<?= esc($p['color']) ?>"
                     style="width:48px;height:36px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer">
            </td>
            <td>
              <input type="text" name="validity_th[<?= esc($p['id']) ?>]"
                     value="<?= esc($p['validity']['th']) ?>"
                     placeholder="ใช้ได้ 24 ชั่วโมง">
            </td>
            <td>
              <input type="text" name="validity_en[<?= esc($p['id']) ?>]"
                     value="<?= esc($p['validity']['en']) ?>"
                     placeholder="Valid 24 hours">
            </td>
            <td>
              <input type="url" name="buy_url[<?= esc($p['id']) ?>]"
                     value="<?= esc($p['buy_url'] ?? '') ?>"
                     placeholder="https://..." style="min-width:160px">
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="admin-card">
    <h2>Pass Usage Note</h2>
    <p style="color:#888;font-size:.88rem;margin-bottom:16px">Shown at the top of the Passes page.</p>
    <div class="form-row">
      <div class="form-group">
        <label>Note (Thai)</label>
        <textarea name="note_th" rows="3"><?= esc($passes['note']['th']) ?></textarea>
      </div>
      <div class="form-group">
        <label>Note (English)</label>
        <textarea name="note_en" rows="3"><?= esc($passes['note']['en']) ?></textarea>
      </div>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">💾 Save All Passes</button>
    <a href="../payment.php" target="_blank" class="btn btn-sm" style="background:#f0f0f0;color:#333;margin-left:8px">🔍 Preview Pass Page</a>
  </div>
</form>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
