<?php
$page_title       = '';
$active_nav       = 'home';
$page_description = 'Phuket Smart Bus – premium public bus connecting Phuket Airport to Rawai Beach & Patong. Live GPS tracking, timetable & Smart Day Pass from ฿299. | ภูเก็ต สมาร์ท บัส บริการรถโดยสารสนามบินภูเก็ต–ราไวย์–ป่าตอง ดูแผนที่สด ตาราง บัตรโดยสาร';
$page_keywords    = 'Phuket Smart Bus,Phuket airport bus,bus Phuket to Rawai,bus Phuket to Patong,Smart Day Pass Phuket,ภูเก็ต สมาร์ท บัส,รถโดยสารสนามบินภูเก็ต,สาย 8357,สาย 8537';
require_once __DIR__ . '/inc/header.php';

$routes = array_filter(load_json('routes.json'), fn($r) => !empty($r['active']));
$passes = load_json('passes.json');
$routes_arr = array_values($routes); // indexed for JS
$all_att = array_values(array_filter(load_json('attractions.json')['attractions'], fn($a) => !empty($a['active'])));
shuffle($all_att);
$featured_att = array_slice($all_att, 0, 4);
?>

<!-- ── Compact Hero ───────────────────────────────────────── -->
<section class="home-hero compact">
  <div class="home-hero-inner">
    <h1><?= esc(t($cfg['hero_title'])) ?></h1>
    <p><?= esc(t($cfg['hero_subtitle'])) ?></p>
    <div class="hero-btns">
      <a href="#live-map" class="btn btn-yellow">📍 <?= $l==='th'?'ดูแผนที่สด':'Live Map' ?></a>
      <a href="#routes" class="btn btn-outline">🗺 <?= $l==='th'?'เลือกเส้นทาง':'Routes' ?></a>
      <a href="timetable.php" class="btn btn-outline">🕐 <?= $l==='th'?'ตาราง':'Timetable' ?></a>
    </div>
  </div>
</section>

<!-- ── Live Map (visible immediately) ────────────────────── -->
<div id="live-map" class="live-map-section">
  <div class="live-map-header">
    <span class="live-dot"></span>
    <?= $l==='th'?'แผนที่ตำแหน่งรถ Real-time':'Live Bus Tracking Map' ?>
  </div>
  <div class="track-wrap">
    <iframe
      src="https://smartbus.phuket.cloud/"
      title="<?= $l==='th'?'แผนที่ติดตามรถสด':'Live Bus Tracking' ?>"
      loading="eager"
      allowfullscreen
    ></iframe>
  </div>
</div>

<!-- ── Route Selector ─────────────────────────────────────── -->
<div id="routes" class="wrap sec">
  <h2 class="sec-title"><?= $l==='th'?'เลือก<span>เส้นทาง</span>':'Select <span>Route</span>' ?></h2>
  <p class="sec-sub"><?= $l==='th'?'เลือกเส้นทางเพื่อดูป้ายจอดและข้อมูลเดินรถ':'Tap a route to see all stops and details' ?></p>

  <!-- Route tab buttons -->
  <div class="route-tab-btns">
    <?php foreach ($routes_arr as $i => $r): ?>
    <button
      class="route-tab-btn <?= $i===0?'active':'' ?>"
      data-route="<?= esc($r['id']) ?>"
      style="--rc:<?= esc($r['color']) ?>"
      onclick="selectRoute('<?= esc($r['id']) ?>', this)"
    >
      <span class="rtb-num" style="background:<?= esc($r['color']) ?>"><?= esc($r['number']) ?></span>
      <span class="rtb-name"><?= esc(t($r['name'])) ?></span>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Route detail panels -->
  <?php foreach ($routes_arr as $i => $r): ?>
  <div class="route-detail <?= $i===0?'active':'' ?>" id="rd-<?= esc($r['id']) ?>">

    <!-- Header -->
    <div class="rd-head" style="background:<?= esc($r['color']) ?>">
      <div>
        <div style="font-size:.75rem;opacity:.75;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">
          <?= $l==='th'?'เส้นทางสาย':'Route' ?>
        </div>
        <div style="font-size:1.8rem;font-weight:900;line-height:1"><?= esc($r['number']) ?></div>
      </div>
      <div style="flex:1;padding-left:20px">
        <div style="font-size:1rem;font-weight:700;margin-bottom:4px"><?= esc(t($r['name'])) ?></div>
        <div style="font-size:.85rem;opacity:.85"><?= esc(t($r['description'])) ?></div>
      </div>
      <div class="rd-head-btns">
        <a href="timetable.php?route=<?= esc($r['id']) ?>" class="btn btn-outline btn-sm">🕐 <?= $l==='th'?'ตาราง':'Schedule' ?></a>
        <a href="tracking.php" class="btn btn-yellow btn-sm">📍 <?= $l==='th'?'ติดตาม':'Track' ?></a>
      </div>
    </div>

    <!-- Stop timeline -->
    <?php if (!empty($r['stops'])): ?>
    <div class="rd-stops">
      <div class="rd-stops-label"><?= $l==='th'?'ป้ายจอดทั้งหมด':'All Stops' ?> (<?= count($r['stops']) ?>)</div>
      <div class="stop-timeline">
        <?php foreach ($r['stops'] as $idx => $s):
          $first = $idx === 0;
          $last  = $idx === count($r['stops'])-1;
        ?>
        <div class="st-item <?= $first?'first':($last?'last':'') ?>">
          <div class="st-line-wrap">
            <div class="st-line-top" style="background:<?= $first?'transparent':esc($r['color']) ?>"></div>
            <div class="st-dot" style="background:<?= esc($r['color']) ?>;border-color:<?= esc($r['color']) ?>">
              <?php if ($first || $last): ?>
              <span style="font-size:.55rem;font-weight:900;color:#fff"><?= $first?'A':'B' ?></span>
              <?php endif; ?>
            </div>
            <div class="st-line-bot" style="background:<?= $last?'transparent':esc($r['color']) ?>"></div>
          </div>
          <a href="<?= base_url('stop.php?id='.$s['id']) ?>" class="st-name <?= ($first||$last)?'terminal':'' ?>"><?= esc(t($s['name'])) ?></a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
  <?php endforeach; ?>
</div>

<!-- ── Pass Prices Strip ──────────────────────────────────── -->
<div class="light-bg">
  <div class="wrap sec" style="padding-top:32px;padding-bottom:32px">
    <h2 class="sec-title" style="text-align:center"><?= $l==='th'?'<span>Smart Day Pass</span> ราคา':'<span>Smart Day Pass</span> Prices' ?></h2>
    <p class="sec-sub" style="text-align:center;margin-bottom:20px"><?= $l==='th'?'ไม่จำกัดเที่ยว เริ่มต้น ฿299':'Unlimited rides from ฿299' ?></p>
    <div class="pass-grid">
      <?php require __DIR__ . '/inc/pass_cards.php'; ?>
    </div>
    <div style="text-align:center;margin-top:20px">
      <a href="payment.php" class="btn btn-teal"><?= $l==='th'?'ดูรายละเอียดบัตรทั้งหมด':'View All Pass Details' ?></a>
    </div>
  </div>
</div>

<!-- ── Featured Attractions ────────────────────────────────── -->
<?php
$routes_map = [];
foreach (load_json('routes.json') as $r) $routes_map[$r['id']] = $r;
?>
<div class="wrap sec">
  <h2 class="sec-title" style="text-align:center"><?= $l==='th'?'<span>สถานที่ท่องเที่ยว</span>ใกล้เคียง':'<span>Nearby</span> Attractions' ?></h2>
  <p class="sec-sub" style="text-align:center;margin-bottom:24px"><?= $l==='th'?'จุดหมายยอดนิยมที่เดินทางด้วย Phuket Smart Bus':'Top spots accessible by Phuket Smart Bus' ?></p>
  <div class="att-grid">
    <?php foreach ($featured_att as $a): ?>
    <?php $a_thumb = !empty($a['images']) ? $a['images'][0] : ($a['image'] ?? ''); ?>
    <a href="<?= base_url('attraction.php?id='.esc($a['id'])) ?>" class="att-card" style="text-decoration:none;color:inherit">
      <?php if ($a_thumb): ?>
      <div class="att-img"><img src="<?= esc($a_thumb) ?>" alt="<?= esc(t($a['name'])) ?>" loading="lazy"></div>
      <?php else: ?>
      <div class="att-img att-img-ph">🏖️</div>
      <?php endif; ?>
      <div class="att-body">
        <div class="att-title"><?= esc(t($a['name'])) ?></div>
        <div class="att-desc"><?= esc(t($a['description'])) ?></div>
        <?php if (!empty($a['nearby'])): ?>
        <div class="att-routes">
          <?php foreach ($a['nearby'] as $nb):
            $r = $routes_map[$nb['route_id']] ?? null;
            if (!$r) continue;
          ?>
          <div class="att-badge" style="--rc:<?= esc($r['color']) ?>">
            <span class="att-badge-num"><?= esc($r['number']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <span class="att-map-btn" style="display:inline-block;margin-top:6px"><?= $l==='th'?'ดูรายละเอียด →':'View Details →' ?></span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <div style="text-align:center;margin-top:4px">
    <a href="attractions.php" class="btn btn-teal"><?= $l==='th'?'ดูสถานที่ทั้งหมด':'See All Attractions' ?></a>
  </div>
</div>

<script>
function selectRoute(id, btn) {
  document.querySelectorAll('.route-tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.route-detail').forEach(d => d.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('rd-' + id).classList.add('active');
}
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
