<?php
$att_id = trim($_GET['id'] ?? '');

require_once __DIR__ . '/inc/helpers.php';

$att_data   = load_json('attractions.json');
$all_routes = load_json('routes.json');

// Find attraction
$att = null;
foreach ($att_data['attractions'] ?? [] as $a) {
    if ($a['id'] === $att_id) { $att = $a; break; }
}

if (!$att || empty($att['active'])) {
    http_response_code(404);
    $page_title = '404 – Not Found';
    require_once __DIR__ . '/inc/header.php';
    echo '<div class="wrap sec"><p>Attraction not found.</p></div>';
    require_once __DIR__ . '/inc/footer.php';
    exit;
}

log_event('attraction', t($att['name']), $att_id);

// Normalise images (support legacy single 'image' field)
$images = !empty($att['images']) ? $att['images'] : (!empty($att['image']) ? [$att['image']] : []);
$thumb  = $images[0] ?? '';

// Build route/stop info for this attraction
$route_map = [];
foreach ($all_routes as $r) $route_map[$r['id']] = $r;

$nearby_info = [];
foreach ($att['nearby'] ?? [] as $nb) {
    $r = $route_map[$nb['route_id']] ?? null;
    if (!$r) continue;
    $stop = null;
    if (!empty($nb['stop_id'])) {
        foreach ($r['stops'] as $s) {
            if ($s['id'] === $nb['stop_id']) { $stop = $s; break; }
        }
    }
    $nearby_info[] = ['route' => $r, 'stop' => $stop];
}

// Related attractions (same routes, excluding self)
$serving_rids = array_column(array_column($nearby_info, 'route'), 'id');
$related = array_slice(array_values(array_filter(
    $att_data['attractions'] ?? [],
    function($a) use ($att_id, $serving_rids) {
        if ($a['id'] === $att_id || empty($a['active'])) return false;
        foreach ($a['nearby'] ?? [] as $nb) {
            if (in_array($nb['route_id'], $serving_rids)) return true;
        }
        return false;
    }
)), 0, 4);

$active_nav       = 'attractions';
$l = $_SESSION['lang'] ?? ($_GET['lang'] ?? 'th');
$att_name         = t($att['name']);
$page_title       = $att_name;
$page_description = t($att['description']);
require_once __DIR__ . '/inc/header.php';
?>

<!-- ── Hero ────────────────────────────────────────────────── -->
<section class="page-hero" style="padding-bottom:28px;<?= $thumb ? 'background-image:linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.6)),url('.esc($thumb).');background-size:cover;background-position:center' : '' ?>">
  <div style="font-size:.8rem;opacity:.75;margin-bottom:10px;text-align:center">
    <a href="<?= base_url() ?>" style="color:inherit">🏠</a> /
    <a href="<?= base_url('attractions.php') ?>" style="color:inherit"><?= $l==='th'?'สถานที่ท่องเที่ยว':'Attractions' ?></a> /
    <?= esc($att_name) ?>
  </div>
  <h1 style="text-align:center;font-size:clamp(1.8rem,5vw,2.8rem);margin-bottom:14px"><?= esc($att_name) ?></h1>

  <!-- Route badges -->
  <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
    <?php foreach ($nearby_info as $ni): ?>
    <span style="background:<?= esc($ni['route']['color']) ?>;color:#fff;border-radius:8px;padding:4px 12px;font-weight:700;font-size:.85rem">
      <?= esc($ni['route']['number']) ?>
      <?php if ($ni['stop']): ?>
      · <?= $l==='th'?'ลงที่':'Alight at' ?> <?= esc(t($ni['stop']['name'])) ?>
      <?php endif; ?>
    </span>
    <?php endforeach; ?>
  </div>
</section>

<div class="wrap">

  <!-- ── Image gallery ─────────────────────────────────────── -->
  <?php if ($images): ?>
  <section class="sec" style="padding-top:32px;padding-bottom:0">
    <?php if (count($images) === 1): ?>
    <div style="border-radius:14px;overflow:hidden;max-height:380px">
      <img src="<?= esc($images[0]) ?>" alt="<?= esc($att_name) ?>" style="width:100%;height:380px;object-fit:cover">
    </div>
    <?php else: ?>
    <!-- Multi-image gallery -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:8px;border-radius:14px;overflow:hidden;max-height:380px">
      <div style="overflow:hidden">
        <img src="<?= esc($images[0]) ?>" alt="<?= esc($att_name) ?>" style="width:100%;height:380px;object-fit:cover;cursor:pointer" onclick="openGallery(0)">
      </div>
      <div style="display:flex;flex-direction:column;gap:8px;overflow:hidden">
        <?php foreach (array_slice($images, 1, 2) as $gi => $img): ?>
        <div style="flex:1;overflow:hidden;position:relative">
          <img src="<?= esc($img) ?>" alt="" style="width:100%;height:100%;object-fit:cover;cursor:pointer" onclick="openGallery(<?= $gi+1 ?>)">
          <?php if ($gi === 1 && count($images) > 3): ?>
          <div onclick="openGallery(2)" style="position:absolute;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem;font-weight:800;cursor:pointer">+<?= count($images)-3 ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Lightbox -->
    <div id="gallery-lb" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;flex-direction:column">
      <button onclick="closeLb()" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:1.8rem;cursor:pointer">✕</button>
      <button onclick="shiftLb(-1)" style="position:absolute;left:16px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:1.6rem;width:44px;height:44px;border-radius:50%;cursor:pointer">‹</button>
      <img id="gallery-lb-img" src="" style="max-width:90vw;max-height:80vh;object-fit:contain;border-radius:8px">
      <div id="gallery-lb-count" style="color:rgba(255,255,255,.6);font-size:.85rem;margin-top:10px"></div>
      <button onclick="shiftLb(1)" style="position:absolute;right:16px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:1.6rem;width:44px;height:44px;border-radius:50%;cursor:pointer">›</button>
    </div>
    <script>
    var _imgs = <?= json_encode(array_values($images)) ?>;
    var _cur = 0;
    function openGallery(i) {
      _cur = i;
      document.getElementById('gallery-lb').style.display = 'flex';
      document.getElementById('gallery-lb-img').src = _imgs[_cur];
      document.getElementById('gallery-lb-count').textContent = (_cur+1) + ' / ' + _imgs.length;
    }
    function closeLb() { document.getElementById('gallery-lb').style.display = 'none'; }
    function shiftLb(d) { _cur = (_cur + d + _imgs.length) % _imgs.length; openGallery(_cur); }
    document.getElementById('gallery-lb').addEventListener('click', function(e){ if(e.target===this) closeLb(); });
    </script>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <!-- ── Description ───────────────────────────────────────── -->
  <section class="sec" style="padding-top:28px;padding-bottom:0">
    <p style="color:var(--mid);line-height:1.8;font-size:1rem"><?= nl2br(esc(t($att['description']))) ?></p>
    <?php if (!empty($att['map_url'])): ?>
    <a href="<?= esc($att['map_url']) ?>" target="_blank" rel="noopener"
       style="display:inline-flex;align-items:center;gap:8px;margin-top:18px;background:var(--teal-bg);border:1px solid rgba(1,170,168,.25);color:var(--teal);border-radius:10px;padding:11px 18px;font-weight:600;font-size:.9rem;text-decoration:none">
      📍 <?= $l==='th'?'ดูแผนที่':'View on Map' ?>
    </a>
    <?php endif; ?>
  </section>

  <!-- ── How to get here ───────────────────────────────────── -->
  <?php if ($nearby_info): ?>
  <section class="sec" style="padding-top:32px;padding-bottom:0">
    <h2 class="sec-title"><?= $l==='th'?'<span>วิธี</span>เดินทาง':'<span>How</span> to Get Here' ?></h2>
    <div style="display:flex;flex-direction:column;gap:12px;margin-top:16px">
      <?php foreach ($nearby_info as $ni):
        $r = $ni['route']; $s = $ni['stop'];
      ?>
      <div style="border:1px solid var(--border);border-radius:12px;overflow:hidden;display:flex;align-items:stretch">
        <div style="background:<?= esc($r['color']) ?>;color:#fff;padding:14px 16px;display:flex;align-items:center;justify-content:center;min-width:64px">
          <span style="font-weight:900;font-size:1rem"><?= esc($r['number']) ?></span>
        </div>
        <div style="padding:12px 16px;flex:1">
          <div style="font-weight:700;font-size:.9rem;margin-bottom:2px"><?= esc(t($r['name'])) ?></div>
          <?php if ($s): ?>
          <div style="font-size:.85rem;color:var(--mid)">
            <?= $l==='th'?'ลงที่ป้าย':'Alight at' ?>
            <a href="<?= base_url('stop.php?id='.$s['id']) ?>" style="color:var(--teal);font-weight:600;text-decoration:none">
              📍 <?= esc(t($s['name'])) ?>
            </a>
          </div>
          <?php else: ?>
          <div style="font-size:.85rem;color:var(--mid)"><?= $l==='th'?'เส้นทางนี้ผ่านใกล้เคียง':'This route passes nearby' ?></div>
          <?php endif; ?>
        </div>
        <a href="<?= base_url('timetable.php?route='.$r['id']) ?>"
           style="display:flex;align-items:center;padding:0 16px;color:var(--teal);font-size:.82rem;font-weight:600;text-decoration:none;border-left:1px solid var(--border)">
          <?= $l==='th'?'ตาราง':'Timetable' ?> →
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ── Related attractions ───────────────────────────────── -->
  <?php if ($related): ?>
  <section class="sec" style="padding-top:32px;padding-bottom:48px">
    <h2 class="sec-title"><?= $l==='th'?'สถานที่<span>ใกล้เคียง</span>':'<span>Nearby</span> Attractions' ?></h2>
    <div class="att-grid" style="margin-top:16px">
      <?php foreach ($related as $ra):
        $ra_imgs = !empty($ra['images']) ? $ra['images'] : (!empty($ra['image']) ? [$ra['image']] : []);
        $ra_thumb = $ra_imgs[0] ?? '';
      ?>
      <a href="<?= base_url('attraction.php?id='.$ra['id']) ?>" class="att-card" style="text-decoration:none;color:inherit">
        <?php if ($ra_thumb): ?>
        <div class="att-img"><img src="<?= esc($ra_thumb) ?>" alt="<?= esc(t($ra['name'])) ?>" loading="lazy"></div>
        <?php else: ?>
        <div class="att-img att-img-ph">🏖️</div>
        <?php endif; ?>
        <div class="att-body">
          <div class="att-title"><?= esc(t($ra['name'])) ?></div>
          <div class="att-desc"><?= esc(t($ra['description'])) ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
