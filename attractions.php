<?php
$page_title       = 'Hotels & Attractions Near Phuket Smart Bus Stops';
$active_nav       = 'attractions';
$page_description = 'Discover top Phuket nearby places accessible by Phuket Smart Bus – beaches, hotels, restaurants and landmarks near every bus stop.';
$page_keywords    = 'Phuket nearby places,Phuket beaches,Phuket hotels,Phuket restaurants,things to do Phuket,สถานที่ใกล้เคียงภูเก็ต';
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

// Category definitions
$categories = [
    'all'        => ['th' => 'ทั้งหมด',           'en' => 'All',             'ico' => '📍'],
    'attraction' => ['th' => 'สถานที่ท่องเที่ยว', 'en' => 'Attraction',      'ico' => '🏖️'],
    'hotel'      => ['th' => 'ที่พัก',             'en' => 'Hotel',           'ico' => '🏨'],
    'restaurant' => ['th' => 'ร้านอาหาร / คาเฟ่',  'en' => 'Restaurant / Café','ico' => '🍽️'],
    'travel'     => ['th' => 'แผนการเดินทาง',      'en' => 'Travel Plan',     'ico' => '✈️'],
];

// Active filters
$cat_filter   = $_GET['cat']   ?? 'all';
$route_filter = $_GET['route'] ?? 'all';
if (!array_key_exists($cat_filter, $categories)) $cat_filter = 'all';

// Build query string helper
function filter_url($cat, $route) {
    $p = [];
    if ($cat   !== 'all') $p['cat']   = $cat;
    if ($route !== 'all') $p['route'] = $route;
    return '?' . ($p ? http_build_query($p) : 'cat=all');
}

// Apply filters
$filtered = array_values(array_filter($attractions, function($a) use ($cat_filter, $route_filter) {
    if ($cat_filter !== 'all' && ($a['category'] ?? 'attraction') !== $cat_filter) return false;
    if ($route_filter !== 'all') {
        $found = false;
        foreach ($a['nearby'] ?? [] as $nb) {
            if ($nb['route_id'] === $route_filter) { $found = true; break; }
        }
        if (!$found) return false;
    }
    return true;
}));
?>

<!-- ── Page Hero -->
<div class="page-hero">
  <h1><span aria-hidden="true">📍 </span><?= $l==='th'?'สถานที่ใกล้เคียง':'Nearby Places' ?></h1>
  <p><?= $l==='th'?'สถานที่ใกล้เคียงที่เดินทางด้วยภูเก็ต สมาร์ท บัสได้':'Places accessible by Phuket Smart Bus' ?></p>
</div>

<div class="wrap sec">

  <!-- ── Category filter (primary) ── -->
  <div class="att-cat-bar">
    <?php foreach ($categories as $cv => $cd): ?>
    <a href="<?= filter_url($cv, $route_filter) ?>"
       class="att-cat-btn <?= $cat_filter===$cv?'active':'' ?>">
      <span class="att-cat-ico"><?= $cd['ico'] ?></span>
      <?= $l==='th'?esc($cd['th']):esc($cd['en']) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- ── Route filter (secondary) ── -->
  <div class="att-filter-bar" style="margin-top:12px">
    <a href="<?= filter_url($cat_filter, 'all') ?>" class="att-filter-btn <?= $route_filter==='all'?'active':'' ?>">
      <?= $l==='th'?'ทุกสาย':'All Routes' ?>
    </a>
    <?php foreach ($routes as $r): ?>
    <a href="<?= filter_url($cat_filter, $r['id']) ?>"
       class="att-filter-btn <?= $route_filter===$r['id']?'active':'' ?>"
       style="--rc:<?= esc($r['color']) ?>">
      <span class="att-fb-num" style="background:<?= esc($r['color']) ?>"><?= esc($r['number']) ?></span>
      <?= esc(t($r['name'])) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- ── Cards grid ── -->
  <?php if ($filtered): ?>
  <div class="att-grid" style="margin-top:24px">
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
      $att_imgs  = !empty($att['images']) ? $att['images'] : (!empty($att['image']) ? [$att['image']] : []);
      $att_thumb = $att_imgs[0] ?? '';
      $att_cat   = $att['category'] ?? 'attraction';
      $cat_ico   = $categories[$att_cat]['ico'] ?? '📍';
    ?>
    <?php $card_href = base_url('attraction.php?id='.esc($att['id'])); ?>
    <div class="att-card" onclick="location.href='<?= $card_href ?>'" style="cursor:pointer">

      <!-- Image -->
      <?php if ($att_thumb): ?>
      <div class="att-img">
        <img src="<?= esc($att_thumb) ?>" alt="<?= esc(t($att['name'])) ?>" loading="lazy">
        <span class="att-cat-badge"><?= $cat_ico ?></span>
      </div>
      <?php else: ?>
      <div class="att-img att-img-ph"><?= $cat_ico ?></div>
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

        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px">
          <a href="<?= $card_href ?>" class="att-map-btn" onclick="event.stopPropagation()">
            <?= $l==='th'?'ดูรายละเอียด →':'View Details →' ?>
          </a>
          <?php if (!empty($att['booking_url'])): ?>
          <a href="<?= esc($att['booking_url']) ?>" target="_blank" rel="noopener"
             class="att-book-btn" onclick="event.stopPropagation()">
            🏨 <?= $l==='th'?'จองเลย':'Book Now' ?>
          </a>
          <?php endif; ?>
        </div>
      </div>

    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <p style="color:#888;padding:40px 0;text-align:center">
    <?= $l==='th'?'ไม่พบสถานที่ในหมวดนี้':'No places found for this filter.' ?>
  </p>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
