<?php
$page_title       = 'สถานที่ท่องเที่ยว / Attractions';
$active_nav       = 'attractions';
$page_description = 'Discover top Phuket attractions accessible by Phuket Smart Bus – beaches, landmarks and entertainment spots near every bus stop. | สถานที่ท่องเที่ยวภูเก็ตเดินทางง่ายด้วยสมาร์ทบัส';
$page_keywords    = 'Phuket attractions,Phuket beaches,things to do Phuket,Patong Karon Kata Surin Rawai,สถานที่ท่องเที่ยวภูเก็ต,หาดภูเก็ต';
require_once __DIR__ . '/inc/header.php';

$routes      = array_values(array_filter(load_json('routes.json'), fn($r) => !empty($r['active'])));
$att_data    = load_json('attractions.json');
$attractions = array_filter($att_data['attractions'] ?? [], fn($a) => !empty($a['active']));

// Build route + stop lookup maps
$route_map  = [];
$stop_names = [];
foreach ($routes as $r) {
    $route_map[$r['id']] = $r;
    foreach ($r['stops'] as $s) {
        $stop_names[$r['id']][$s['id']] = t($s['name']);
    }
}

// Active route filter
$filter = $_GET['route'] ?? 'all';
$filtered = array_values(array_filter($attractions, function($a) use ($filter) {
    if ($filter === 'all') return true;
    foreach ($a['nearby'] ?? [] as $nb) {
        if ($nb['route_id'] === $filter) return true;
    }
    return false;
}));
?>

<!-- ── Page Hero -->
<div class="page-hero">
  <h1>🗺️ <?= $l==='th'?'สถานที่ท่องเที่ยว':'Attractions' ?></h1>
  <p><?= $l==='th'?'สถานที่น่าเที่ยวที่เดินทางด้วยภูเก็ต สมาร์ท บัสได้':'Top spots accessible by Phuket Smart Bus' ?></p>
</div>

<div class="wrap sec">

  <!-- ── Route filter ── -->
  <div class="att-filter-bar">
    <a href="?route=all" class="att-filter-btn <?= $filter==='all'?'active':'' ?>">
      <?= $l==='th'?'ทั้งหมด':'All' ?>
    </a>
    <?php foreach ($routes as $r): ?>
    <a href="?route=<?= esc($r['id']) ?>"
       class="att-filter-btn <?= $filter===$r['id']?'active':'' ?>"
       style="--rc:<?= esc($r['color']) ?>">
      <span class="att-fb-num" style="background:<?= esc($r['color']) ?>"><?= esc($r['number']) ?></span>
      <?= esc(t($r['name'])) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- ── Cards grid ── -->
  <?php if ($filtered): ?>
  <div class="att-grid">
    <?php foreach ($filtered as $att):
      $labels = [];
      foreach ($att['nearby'] ?? [] as $nb) {
          $r = $route_map[$nb['route_id']] ?? null;
          if (!$r) continue;
          $labels[] = [
              'color'      => $r['color'],
              'num'        => $r['number'],
              'stop'       => $stop_names[$nb['route_id']][$nb['stop_id']] ?? '',
              'route_name' => t($r['name']),
          ];
      }
    ?>
    <?php
      $att_imgs = !empty($att['images']) ? $att['images'] : (!empty($att['image']) ? [$att['image']] : []);
      $att_thumb = $att_imgs[0] ?? '';
    ?>
    <a href="<?= base_url('attraction.php?id='.esc($att['id'])) ?>" class="att-card" style="text-decoration:none;color:inherit">

      <!-- Image -->
      <?php if ($att_thumb): ?>
      <div class="att-img">
        <img src="<?= esc($att_thumb) ?>" alt="<?= esc(t($att['name'])) ?>" loading="lazy">
      </div>
      <?php else: ?>
      <div class="att-img att-img-ph">🏖️</div>
      <?php endif; ?>

      <div class="att-body">
        <h3 class="att-title"><?= esc(t($att['name'])) ?></h3>
        <p class="att-desc"><?= esc(t($att['description'])) ?></p>

        <!-- Route + stop badges -->
        <?php if ($labels): ?>
        <div class="att-routes">
          <?php foreach ($labels as $lb): ?>
          <div class="att-badge" style="--rc:<?= esc($lb['color']) ?>">
            <span class="att-badge-num"><?= esc($lb['num']) ?></span>
            <?php if ($lb['stop']): ?>
            <span class="att-badge-stop"><?= $l==='th'?'ลงที่':'Alight at' ?> <?= esc($lb['stop']) ?></span>
            <?php else: ?>
            <span class="att-badge-stop"><?= esc($lb['route_name']) ?></span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <span class="att-map-btn" style="display:inline-block;margin-top:8px">
          <?= $l==='th'?'ดูรายละเอียด →':'View Details →' ?>
        </span>
      </div>

    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <p style="color:#888;padding:40px 0;text-align:center">
    <?= $l==='th'?'ไม่พบสถานที่ท่องเที่ยวในเส้นทางนี้':'No attractions found for this route.' ?>
  </p>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
