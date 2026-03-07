<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

$admin_page_title  = 'Dashboard';
$admin_active_nav  = 'dashboard';
require_once __DIR__ . '/inc/admin_header.php';

$routes    = load_json('routes.json');
$passes    = load_json('passes.json');
$tt        = load_json('timetable.json');
$tt_imgs   = load_json('timetable_images.json');
$cfg       = load_json('config.json');

$active_routes = count(array_filter($routes, fn($r) => !empty($r['active'])));
$total_trips   = 0;
foreach ($tt as $k => $v) {
    if (isset($v['trips'])) $total_trips += count($v['trips']);
}
?>

<div class="admin-stats">
  <div class="stat-card">
    <div class="stat-num"><?= $active_routes ?></div>
    <div class="stat-lbl">Active Routes</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= count($passes['passes']) ?></div>
    <div class="stat-lbl">Pass Types</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= $total_trips ?></div>
    <div class="stat-lbl">Total Trips</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= count($tt_imgs['images'] ?? []) ?></div>
    <div class="stat-lbl">Timetable Images</div>
  </div>
</div>

<!-- Quick links -->
<div class="quick-links">
  <?php
  $quick = [
    ['timetable.php','🕐','Edit Timetable','Update trip times'],
    ['tt_images.php','🖼','Timetable Images','Upload new images'],
    ['routes.php','🗺','Manage Routes','Add/edit routes'],
    ['passes.php','🎫','Manage Passes','Update prices & colors'],
    ['content.php','✏️','Edit Content','Site text & info'],
    ['users.php','👤','Manage Users','Admin accounts'],
  ];
  foreach ($quick as [$href, $icon, $title, $desc]):
  ?>
  <a href="<?= esc($href) ?>" class="quick-link">
    <div class="ql-icon"><?= $icon ?></div>
    <div class="ql-title"><?= esc($title) ?></div>
    <div class="ql-desc"><?= esc($desc) ?></div>
  </a>
  <?php endforeach; ?>
</div>

<!-- Route status -->
<div class="admin-card">
  <h2>Routes Status</h2>
  <table class="admin-table">
    <thead>
      <tr><th>Number</th><th>Name (EN)</th><th>Stops</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($routes as $r): ?>
      <tr>
        <td><span style="background:<?= esc($r['color']) ?>;color:#fff;padding:2px 10px;border-radius:4px;font-weight:700"><?= esc($r['number']) ?></span></td>
        <td><?= esc($r['name']['en']) ?></td>
        <td><?= count($r['stops']) ?></td>
        <td>
          <span style="background:<?= $r['active']?'#27ae60':'#e74c3c' ?>;color:#fff;padding:2px 10px;border-radius:12px;font-size:.78rem;font-weight:700">
            <?= $r['active'] ? 'Active' : 'Inactive' ?>
          </span>
        </td>
        <td><a href="routes.php?edit=<?= esc($r['id']) ?>" style="color:var(--teal)">Edit</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
