<?php
$pass_id = trim($_GET['id'] ?? '');

require_once __DIR__ . '/inc/helpers.php';

$passes_data = load_json('passes.json');
$all_routes  = array_filter(load_json('routes.json'), fn($r) => !empty($r['active']));

// Find pass
$pass = null;
foreach ($passes_data['passes'] as $p) {
    if ($p['id'] === $pass_id) { $pass = $p; break; }
}

if (!$pass) {
    http_response_code(404);
    $page_title = '404 – Not Found';
    require_once __DIR__ . '/inc/header.php';
    echo '<div class="wrap sec"><p>Pass not found. <a href="payment.php">View all passes</a></p></div>';
    require_once __DIR__ . '/inc/footer.php';
    exit;
}

// Which routes this pass covers
$pass_route_ids = !empty($pass['routes']) ? $pass['routes'] : [];
$valid_routes   = empty($pass_route_ids)
    ? $all_routes
    : array_filter($all_routes, fn($r) => in_array($r['id'], $pass_route_ids));

$color   = $pass['color'];
$p_img   = $pass['image'] ?? '';

$active_nav = 'payment';
$l          = $_SESSION['lang'] ?? ($_GET['lang'] ?? 'th');

preg_match('/(\d+)/', t($pass['name']), $m);
$days    = $m[1] ?? '';
$day_lbl = $days ? ($days == 1 ? ($l==='th'?'วัน':'day') : ($l==='th'?'วัน':'days')) : '';
$pass_name        = t($pass['name']);
$page_title       = $pass_name . ' Smart Day Pass';
$page_description = esc(t($pass['validity'])) . ' – ฿' . number_format($pass['price']);
require_once __DIR__ . '/inc/header.php';
?>

<!-- ── Hero ────────────────────────────────────────────────── -->
<section class="page-hero" style="background:linear-gradient(135deg,<?= esc($color) ?> 0%,<?= esc($color) ?>bb 100%);padding-bottom:32px">
  <div style="font-size:.8rem;opacity:.75;margin-bottom:10px;text-align:center">
    <a href="<?= base_url() ?>" style="color:inherit">🏠</a> /
    <a href="<?= base_url('payment.php') ?>" style="color:inherit"><?= $l==='th'?'บัตรโดยสาร':'Passes' ?></a> /
    <?= esc($pass_name) ?>
  </div>
  <div style="display:flex;align-items:center;justify-content:center;gap:6px;font-size:.65rem;font-weight:800;letter-spacing:1.8px;text-transform:uppercase;color:rgba(255,255,255,.7);margin-bottom:6px">
    Smart Day Pass
  </div>
  <h1 style="text-align:center;font-size:clamp(2.8rem,10vw,5rem);line-height:1;margin-bottom:6px">
    <?= esc($days ?: $pass_name) ?>
    <span style="font-size:clamp(1rem,4vw,1.8rem);font-weight:700"><?= esc($day_lbl) ?></span>
  </h1>
  <p style="text-align:center;font-size:clamp(1.4rem,5vw,2.2rem);font-weight:900;margin-bottom:6px">
    <sup style="font-size:.55em;vertical-align:super">฿</sup><?= number_format($pass['price']) ?>
  </p>
  <p style="text-align:center;font-size:.9rem;opacity:.85"><?= esc(t($pass['validity'])) ?></p>

  <!-- Route badges -->
  <?php if ($valid_routes): ?>
  <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:18px">
    <?php foreach ($valid_routes as $r): ?>
    <span style="background:rgba(255,255,255,.25);color:#fff;border-radius:8px;padding:5px 14px;font-weight:800;font-size:.9rem;backdrop-filter:blur(4px)">
      <?= esc($r['number']) ?>
    </span>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<div class="wrap">

  <!-- ── Image ─────────────────────────────────────────────── -->
  <?php if ($p_img): ?>
  <section class="sec" style="padding-top:32px;padding-bottom:0">
    <div style="border-radius:16px;overflow:hidden;max-height:420px">
      <img src="<?= esc($p_img) ?>" alt="<?= esc($pass_name) ?>" style="width:100%;height:420px;object-fit:cover">
    </div>
  </section>
  <?php endif; ?>

  <!-- ── Details ───────────────────────────────────────────── -->
  <section class="sec" style="padding-top:32px;padding-bottom:0">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px">

      <div style="background:var(--light);border-radius:14px;padding:20px 22px">
        <div style="font-size:.72rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--mid);margin-bottom:6px"><?= $l==='th'?'ระยะเวลา':'Validity' ?></div>
        <div style="font-size:1.2rem;font-weight:700;color:var(--dark)"><?= esc(t($pass['validity'])) ?></div>
      </div>

      <div style="background:var(--light);border-radius:14px;padding:20px 22px">
        <div style="font-size:.72rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--mid);margin-bottom:6px"><?= $l==='th'?'เส้นทาง':'Routes Covered' ?></div>
        <div style="display:flex;gap:6px;flex-wrap:wrap">
          <?php if (empty($pass_route_ids)): ?>
          <span style="font-size:.95rem;font-weight:700;color:var(--teal)"><?= $l==='th'?'ทุกเส้นทาง':'All Routes' ?></span>
          <?php else: foreach ($valid_routes as $r): ?>
          <span style="background:<?= esc($r['color']) ?>;color:#fff;border-radius:6px;padding:3px 10px;font-weight:800;font-size:.85rem"><?= esc($r['number']) ?></span>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <div style="background:var(--light);border-radius:14px;padding:20px 22px">
        <div style="font-size:.72rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--mid);margin-bottom:6px"><?= $l==='th'?'จำนวนเที่ยว':'Rides' ?></div>
        <div style="font-size:1.2rem;font-weight:700;color:var(--dark)"><?= $l==='th'?'ไม่จำกัด':'Unlimited' ?></div>
      </div>

    </div>
  </section>

  <!-- ── Note / Description ────────────────────────────────── -->
  <?php if (!empty($passes_data['note'])): ?>
  <section class="sec" style="padding-top:24px;padding-bottom:0">
    <p style="background:var(--teal-bg);border-left:4px solid var(--teal);border-radius:6px;padding:12px 16px;font-size:.9rem">
      ℹ️ <?= esc(t($passes_data['note'])) ?>
    </p>
  </section>
  <?php endif; ?>

  <!-- ── Buy ───────────────────────────────────────────────── -->
  <section class="sec" style="padding-top:24px;padding-bottom:0;text-align:center">
    <?php if (!empty($pass['buy_url'])): ?>
    <a href="<?= esc($pass['buy_url']) ?>" target="_blank" rel="noopener"
       class="btn btn-teal" style="font-size:1.1rem;padding:14px 36px;background:<?= esc($color) ?>;border-color:<?= esc($color) ?>">
      🛒 <?= $l==='th'?'ซื้อบัตรโดยสาร':'Buy This Pass' ?> — ฿<?= number_format($pass['price']) ?>
    </a>
    <?php else: ?>
    <p style="font-size:.95rem;color:var(--mid)"><?= $l==='th'?'ซื้อได้บนรถ (เงินสด / QR Code / บัตรเครดิต)':'Purchase on-board (Cash / QR Code / Credit Card)' ?></p>
    <?php endif; ?>
    <div style="margin-top:14px">
      <a href="<?= base_url('payment.php') ?>" style="color:var(--mid);font-size:.88rem">
        ← <?= $l==='th'?'ดูบัตรโดยสารทั้งหมด':'View all passes' ?>
      </a>
    </div>
  </section>

  <!-- ── Valid routes detail ───────────────────────────────── -->
  <?php if ($valid_routes): ?>
  <section class="sec" style="padding-top:32px;padding-bottom:0">
    <h2 class="sec-title"><?= $l==='th'?'เส้นทาง<span>ที่ใช้ได้</span>':'Valid <span>Routes</span>' ?></h2>
    <div style="display:flex;flex-direction:column;gap:12px;margin-top:16px">
      <?php foreach ($valid_routes as $r): ?>
      <div style="border:1px solid var(--border);border-radius:12px;overflow:hidden;display:flex;align-items:stretch">
        <div style="background:<?= esc($r['color']) ?>;color:#fff;padding:14px 16px;display:flex;align-items:center;justify-content:center;min-width:64px">
          <span style="font-weight:900;font-size:1rem"><?= esc($r['number']) ?></span>
        </div>
        <div style="padding:12px 16px;flex:1">
          <div style="font-weight:700;font-size:.9rem;margin-bottom:2px"><?= esc(t($r['name'])) ?></div>
          <div style="font-size:.82rem;color:var(--mid)"><?= esc(t($r['description'])) ?></div>
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

  <!-- ── Other passes ──────────────────────────────────────── -->
  <section class="sec" style="padding-top:32px;padding-bottom:48px">
    <h2 class="sec-title"><?= $l==='th'?'บัตร<span>อื่นๆ</span>':'Other <span>Passes</span>' ?></h2>
    <div class="pass-grid" style="margin-top:16px">
      <?php
      $routes = $all_routes; // needed by pass_cards.php
      $passes = ['passes' => array_values(array_filter($passes_data['passes'], fn($p) => $p['id'] !== $pass_id))];
      require __DIR__ . '/inc/pass_cards.php';
      ?>
    </div>
  </section>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
