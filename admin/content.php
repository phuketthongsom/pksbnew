<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

$admin_page_title = 'Edit Content';
$admin_active_nav = 'content';

$cfg = load_json('config.json');
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cfg['hero_title']   = ['th' => trim($_POST['hero_title_th']),   'en' => trim($_POST['hero_title_en'])];
    $cfg['hero_subtitle']= ['th' => trim($_POST['hero_sub_th']),     'en' => trim($_POST['hero_sub_en'])];
    $cfg['about_text']   = ['th' => trim($_POST['about_th']),        'en' => trim($_POST['about_en'])];
    $cfg['phone']        = trim($_POST['phone']);
    $cfg['fax']          = trim($_POST['fax']);
    $cfg['email']        = trim($_POST['email']);
    $cfg['line_id']      = trim($_POST['line_id']);
    $cfg['address']      = trim($_POST['address']);
    $cfg['operating_hours'] = ['th' => trim($_POST['hours_th']), 'en' => trim($_POST['hours_en'])];
    $cfg['announcement'] = [
        'active' => !empty($_POST['ann_active']),
        'text'   => ['th' => trim($_POST['ann_th']), 'en' => trim($_POST['ann_en'])],
    ];
    $cfg['facebook_page_id'] = trim($_POST['facebook_page_id'] ?? '');
    save_json('config.json', $cfg);
    $msg = 'Content saved.';
}

require_once __DIR__ . '/inc/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= esc($msg) ?></div><?php endif; ?>

<form method="post">
  <div class="admin-card">
    <h2>Hero Banner Text</h2>
    <div class="form-row">
      <div class="form-group"><label>Title (Thai)</label><input type="text" name="hero_title_th" value="<?= esc($cfg['hero_title']['th']) ?>"></div>
      <div class="form-group"><label>Title (English)</label><input type="text" name="hero_title_en" value="<?= esc($cfg['hero_title']['en']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Subtitle (Thai)</label><input type="text" name="hero_sub_th" value="<?= esc($cfg['hero_subtitle']['th']) ?>"></div>
      <div class="form-group"><label>Subtitle (English)</label><input type="text" name="hero_sub_en" value="<?= esc($cfg['hero_subtitle']['en']) ?>"></div>
    </div>
  </div>

  <div class="admin-card">
    <h2>About Us Text</h2>
    <div class="form-row">
      <div class="form-group"><label>About (Thai)</label><textarea name="about_th" rows="4"><?= esc($cfg['about_text']['th']) ?></textarea></div>
      <div class="form-group"><label>About (English)</label><textarea name="about_en" rows="4"><?= esc($cfg['about_text']['en']) ?></textarea></div>
    </div>
  </div>

  <div class="admin-card">
    <h2>Contact Information</h2>
    <div class="form-row">
      <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= esc($cfg['phone']) ?>"></div>
      <div class="form-group"><label>Fax</label><input type="text" name="fax" value="<?= esc($cfg['fax']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= esc($cfg['email']) ?>"></div>
      <div class="form-group"><label>LINE ID</label><input type="text" name="line_id" value="<?= esc($cfg['line_id']) ?>"></div>
    </div>
    <div class="form-group"><label>Address</label><input type="text" name="address" value="<?= esc($cfg['address']) ?>"></div>
    <div class="form-row">
      <div class="form-group"><label>Service Hours (Thai)</label><input type="text" name="hours_th" value="<?= esc($cfg['operating_hours']['th']) ?>"></div>
      <div class="form-group"><label>Service Hours (English)</label><input type="text" name="hours_en" value="<?= esc($cfg['operating_hours']['en']) ?>"></div>
    </div>
  </div>

  <div class="admin-card">
    <h2>Announcement Bar</h2>
    <div class="form-group">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="ann_active" value="1" <?= !empty($cfg['announcement']['active'])?'checked':'' ?>>
        Show announcement bar
      </label>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Announcement (Thai)</label><input type="text" name="ann_th" value="<?= esc($cfg['announcement']['text']['th'] ?? '') ?>"></div>
      <div class="form-group"><label>Announcement (English)</label><input type="text" name="ann_en" value="<?= esc($cfg['announcement']['text']['en'] ?? '') ?>"></div>
    </div>
  </div>

  <div class="admin-card">
    <h2>💬 Facebook Messenger Chat Plugin</h2>
    <p style="font-size:.85rem;color:#666;margin-bottom:14px">
      Enter your Facebook <strong>Page ID</strong> (numeric, e.g. <code>123456789012345</code>) to show the Messenger chat bubble on all pages.
      Leave blank to disable. Find your Page ID in Facebook → Page Settings → About.
    </p>
    <div class="form-group">
      <label>Facebook Page ID</label>
      <input type="text" name="facebook_page_id" value="<?= esc($cfg['facebook_page_id'] ?? '') ?>" placeholder="e.g. 123456789012345" style="font-family:monospace">
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">💾 Save All Content</button>
  </div>
</form>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
