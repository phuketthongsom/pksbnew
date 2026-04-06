<?php
$page_title       = 'Live Bus Tracking – Phuket Smart Bus GPS Map';
$active_nav       = 'tracking';
$page_description = 'Track Phuket Smart Bus in real-time. Live GPS map showing bus locations on route 8357 Airport–Rawai and route 8537 Terminal–Patong.';
$page_keywords    = 'Phuket bus tracker,live bus tracking Phuket,Phuket Smart Bus location,GPS bus Phuket,ติดตามรถ ภูเก็ต,แผนที่รถโดยสารภูเก็ต';
require_once __DIR__ . '/inc/header.php';

$routes = array_filter(load_json('routes.json'), fn($r) => !empty($r['active']));
?>

<!-- ── Page Hero -->
<div class="page-hero">
  <h1><span aria-hidden="true">📍 </span><?= $l==='th'?'ตำแหน่งรถสด':'Live Bus Tracking' ?></h1>
  <p><?= $l==='th'?'ติดตามตำแหน่งรถแบบ Real-time อัปเดตอัตโนมัติ':'Real-time bus locations updated automatically' ?></p>
</div>

<!-- ── Live Map iframe -->
<div class="wrap sec" style="padding-top:24px">
  <div class="track-wrap">
    <iframe
      src="https://smartbus.phuket.cloud/"
      title="<?= $l==='th'?'แผนที่ติดตามรถสด':'Live Bus Tracking Map' ?>"
      loading="eager"
      allowfullscreen
    ></iframe>
  </div>
  <p class="track-note">🔴 <?= $l==='th'?'อัปเดตอัตโนมัติ — Powered by smartbus.phuket.cloud':'Auto-updating live map — Powered by smartbus.phuket.cloud' ?></p>
</div>

<!-- ── Bus Stop List -->
<div class="wrap sec" style="padding-top:0">
  <h2 class="sec-title"><?= $l==='th'?'ป้ายจอด<span>ตามเส้นทาง</span>':'Bus <span>Stops</span>' ?></h2>
  <p class="sec-sub"><?= $l==='th'?'ป้ายจอดทั้งหมดในแต่ละเส้นทาง':'All stops for each route' ?></p>

  <?php foreach ($routes as $r): ?>
  <?php if (empty($r['stops'])) continue; ?>
  <div class="stop-list">
    <h3>
      <span style="background:<?= esc($r['color']) ?>;color:#fff;border-radius:4px;padding:2px 8px;font-size:.8rem"><?= esc($r['number']) ?></span>
      <?= esc(t($r['name'])) ?>
    </h3>
    <div class="stop-chips-num">
      <?php foreach ($r['stops'] as $i => $s): ?>
      <span class="stop-n">
        <span class="n" style="background:<?= esc($r['color']) ?>"><?= $i+1 ?></span>
        <?= esc(t($s['name'])) ?>
      </span>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
