<?php
$page_title       = 'เวลาเดินรถ / Timetable';
$active_nav       = 'timetable';
$page_description = 'Phuket Smart Bus timetable – full schedules for route 8357 Airport to Rawai and route 8537 Terminal to Patong. Daily service 06:00–20:00. | ตารางเดินรถ ภูเก็ต สมาร์ท บัส สาย 8357 และ 8537 ทุกวัน 06:00-20:00';
$page_keywords    = 'Phuket bus timetable,Phuket bus schedule,route 8357 timetable,route 8537 timetable,airport bus Phuket schedule,ตารางรถโดยสารภูเก็ต';
require_once __DIR__ . '/inc/header.php';

$routes    = array_values(array_filter(load_json('routes.json'), fn($r) => !empty($r['active'])));
$tt_images = load_json('timetable_images.json');

// Active route
$active_rid   = $_GET['route'] ?? ($routes[0]['id'] ?? '');
$active_route = null;
foreach ($routes as $r) {
    if ($r['id'] === $active_rid) { $active_route = $r; break; }
}
if (!$active_route) { $active_route = $routes[0] ?? null; $active_rid = $active_route['id'] ?? ''; }

// Stops & direction
$active_dir = $_GET['dir'] ?? 'out';
$stops      = $active_route['stops'] ?? [];
$n          = count($stops);
$stops_vis  = ($active_dir === 'ret' && $n > 0) ? array_reverse($stops) : $stops;
?>

<!-- ── Page Hero -->
<div class="page-hero">
  <h1>🕐 <?= $l==='th'?'ตารางเดินรถ':'Bus Timetable' ?></h1>
  <p><?= $l==='th'?'ตรวจสอบเวลาออกรถก่อนเดินทาง':'Check departure times before you travel' ?></p>
</div>

<div class="wrap sec">

  <!-- ── Route selector ── -->
  <div class="tt-route-selector">
    <?php foreach ($routes as $r): ?>
    <a href="?route=<?= esc($r['id']) ?>"
       class="tt-rs-btn <?= $r['id']===$active_rid?'active':'' ?>"
       style="--rc:<?= esc($r['color']) ?>">
      <span class="tt-rs-num" style="background:<?= esc($r['color']) ?>"><?= esc($r['number']) ?></span>
      <span class="tt-rs-name"><?= esc(t($r['name'])) ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if ($active_route): ?>

  <!-- ── Route info bar ── -->
  <div class="tt-route-bar" style="background:<?= esc($active_route['color']) ?>">
    <div>
      <div class="tt-rb-num"><?= esc($active_route['number']) ?></div>
      <div class="tt-rb-name"><?= esc(t($active_route['name'])) ?></div>
      <div class="tt-rb-desc"><?= esc(t($active_route['description'])) ?></div>
    </div>
    <div class="tt-rb-actions">
      <a href="tracking.php" class="btn btn-yellow btn-sm">📍 <?= $l==='th'?'ติดตาม':'Track' ?></a>
    </div>
  </div>

  <!-- ── Direction toggle ── -->
  <?php if ($n > 0):
    $f  = esc(t($stops[0]['name']));
    $la = esc(t($stops[$n-1]['name']));
  ?>
  <div class="tt-dir-tabs">
    <a href="?route=<?= esc($active_rid) ?>&dir=out"
       class="tt-dir-btn <?= $active_dir!=='ret'?'active':'' ?>"
       style="--rc:<?= esc($active_route['color']) ?>">
      ➡ <?= $f ?> → <?= $la ?>
    </a>
    <a href="?route=<?= esc($active_rid) ?>&dir=ret"
       class="tt-dir-btn <?= $active_dir==='ret'?'active':'' ?>"
       style="--rc:<?= esc($active_route['color']) ?>">
      ⬅ <?= $la ?> → <?= $f ?>
    </a>
  </div>
  <?php endif; ?>

  <!-- ── S-curve stop visual ── -->
  <?php if ($stops_vis): ?>
  <div class="rv" style="--rc:<?= esc($active_route['color']) ?>">
    <?php foreach ($stops_vis as $i => $s):
      $is_t = ($i === 0 || $i === $n - 1);
      $is_l = ($i % 2 === 0);
      $nm   = esc(t($s['name']));
    ?>
    <div class="rv-stop<?= $is_t?' rv-t':'' ?><?= $is_l?' rv-l':' rv-r' ?>">
      <?php if ($is_l): ?>
        <div class="rv-lbl"><?= $nm ?></div>
        <div class="rv-pin"><span class="rv-dot"></span></div>
        <div class="rv-sp"></div>
      <?php else: ?>
        <div class="rv-sp"></div>
        <div class="rv-pin"><span class="rv-dot"></span></div>
        <div class="rv-lbl"><?= $nm ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <p style="color:#888;padding:20px 0"><?= $l==='th'?'ยังไม่มีข้อมูลเส้นทาง':'No route stops available.' ?></p>
  <?php endif; ?>

  <!-- ── Timetable images ── -->
  <?php
    $route_imgs = array_filter($tt_images['images'] ?? [], fn($i) => !empty($i['active']) && ($i['route']===$active_rid || $i['route']==='all'));
  ?>
  <?php if ($route_imgs): ?>
  <div style="margin-top:28px">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:12px">📄 <?= $l==='th'?'ตารางเวลาเป็นรูปภาพ':'Timetable Images' ?></h3>
    <div class="tt-img-grid">
      <?php foreach ($route_imgs as $img): ?>
      <div class="tt-img-item">
        <a href="<?= esc($img['file']) ?>" target="_blank">
          <img src="<?= esc($img['file']) ?>" alt="<?= esc(t($img['label'])) ?>" loading="lazy">
        </a>
        <p><?= esc(t($img['label'])) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
