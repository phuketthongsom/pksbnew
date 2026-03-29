<?php
$page_title       = 'บัตรโดยสาร / Passes';
$active_nav       = 'payment';
$page_description = 'Smart Day Pass for Phuket Smart Bus – unlimited rides from ฿299/day. Valid on routes 8357 Airport–Rawai & 8537 Terminal–Patong. Buy on-board or contact us.';
$page_keywords    = 'Smart Day Pass Phuket,Phuket bus pass,unlimited bus Phuket,bus ticket Phuket price,บัตรโดยสารภูเก็ต,สมาร์ท เดย์ พาส';
require_once __DIR__ . '/inc/header.php';

$passes = load_json('passes.json');
$routes = array_filter(load_json('routes.json'), fn($r) => !empty($r['active']));
?>

<!-- ── Page Hero -->
<div class="page-hero">
  <h1><span aria-hidden="true">💳 </span><?= $l==='th'?'บัตรโดยสาร Smart Day Pass':'Smart Day Pass' ?></h1>
  <p><?= $l==='th'?'เลือกแผนที่เหมาะกับการเดินทางของคุณ':'Choose the pass that fits your trip' ?></p>
</div>

<div class="wrap sec">

  <!-- Note -->
  <p style="background:rgba(1,170,168,.12);border-left:4px solid var(--teal);border-radius:10px;padding:12px 16px;font-size:.9rem;margin-bottom:28px;color:var(--mid)">
    ℹ️ <?= esc(t($passes['note'])) ?>
  </p>

  <!-- Pass price cards -->
  <h2 class="sec-title"><?= $l==='th'?'ราคา<span>บัตรโดยสาร</span>':'Pass <span>Prices</span>' ?></h2>
  <p class="sec-sub" style="margin-bottom:18px"><?= $l==='th'?'ใช้ได้ไม่จำกัดเที่ยวตลอดระยะเวลาที่เลือก':'Unlimited rides for the duration selected' ?></p>
  <div class="pass-grid" style="margin-bottom:40px">
    <?php require __DIR__ . '/inc/pass_cards.php'; ?>
  </div>

  <!-- Valid routes -->
  <h2 class="sec-title"><?= $l==='th'?'เส้นทาง<span>ที่ใช้ได้</span>':'Valid <span>Routes</span>' ?></h2>
  <p class="sec-sub"><?= $l==='th'?'บัตรโดยสารใช้ได้กับเส้นทางเหล่านี้':'Your pass is valid on these routes' ?></p>
  <div class="route-grid" style="margin-bottom:40px">
    <?php foreach ($routes as $r): ?>
    <div class="route-card">
      <div class="route-card-head" style="background:<?= esc($r['color']) ?>">
        <div>
          <div class="route-label"><?= $l==='th'?'สาย':'Route' ?></div>
          <div class="route-num"><?= esc($r['number']) ?></div>
        </div>
      </div>
      <div class="route-card-body">
        <h3><?= esc(t($r['name'])) ?></h3>
        <p><?= esc(t($r['description'])) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Payment methods -->
  <h2 class="sec-title"><?= $l==='th'?'วิธี<span>ชำระเงิน</span>':'Payment <span>Methods</span>' ?></h2>
  <p class="sec-sub"><?= $l==='th'?'ช่องทางการชำระเงินที่รองรับ':'Accepted payment methods' ?></p>
  <div class="method-grid">
    <?php
    $methods = [
      ['ico'=>'💵','th'=>'ชำระบนรถ (เงินสด)','en'=>'On-board Cash'],
      ['ico'=>'🏧','th'=>'QR Code','en'=>'QR Code'],
      ['ico'=>'💳','th'=>'บัตรเครดิต/เดบิต','en'=>'Credit/Debit Card'],
    ];
    foreach ($methods as $m): ?>
    <div class="method-card">
      <span class="ico"><?= $m['ico'] ?></span>
      <span><?= $l==='th'?esc($m['th']):esc($m['en']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Contact CTA -->
  <div style="margin-top:40px;background:linear-gradient(135deg,var(--teal-bg),#fff);border:1px solid rgba(1,170,168,.2);border-radius:16px;padding:28px 24px;text-align:center">
    <div style="font-size:1.5rem;margin-bottom:8px">🎟️</div>
    <h2 style="font-size:1.15rem;font-weight:800;margin-bottom:6px;color:var(--dark)">
      <?= $l==='th'?'สนใจซื้อบัตรล่วงหน้า?':'Want to book in advance?' ?>
    </h2>
    <p style="font-size:.9rem;color:var(--mid);margin-bottom:20px">
      <?= $l==='th'?'ติดต่อเราผ่าน LINE หรือโทรศัพท์ เพื่อสำรองบัตรและสอบถามข้อมูลเพิ่มเติม':'Contact us via LINE or phone to reserve passes and get more information' ?>
    </p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="https://line.me/ti/p/~<?= esc($cfg['line_id']) ?>" target="_blank" rel="noopener"
         style="display:inline-flex;align-items:center;gap:8px;background:#06c755;color:#fff;border-radius:12px;padding:12px 22px;font-weight:700;font-size:.95rem;text-decoration:none">
        💬 LINE: <?= esc($cfg['line_id']) ?>
      </a>
      <a href="tel:<?= esc(preg_replace('/[^0-9+]/','',$cfg['phone'])) ?>"
         style="display:inline-flex;align-items:center;gap:8px;background:var(--teal);color:#fff;border-radius:12px;padding:12px 22px;font-weight:700;font-size:.95rem;text-decoration:none">
        📞 <?= esc($cfg['phone']) ?>
      </a>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
