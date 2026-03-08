<?php
$stop_id = trim($_GET['id'] ?? '');

require_once __DIR__ . '/inc/helpers.php';

// ── Find all routes that contain this stop ──────────────────
$all_routes = load_json('routes.json');
$timetable  = load_json('timetable.json');
$att_data   = load_json('attractions.json');
$stops_data = load_json('stops.json');
$stop_extra = $stops_data['stops'][$stop_id] ?? [];

$serving_routes = []; // [['route'=>$r, 'stop'=>$s, 'idx'=>$i], ...]
$stop_info = null;

foreach ($all_routes as $r) {
    foreach ($r['stops'] as $idx => $s) {
        if ($s['id'] === $stop_id) {
            $serving_routes[] = ['route' => $r, 'stop' => $s, 'idx' => $idx];
            if (!$stop_info) $stop_info = $s;
        }
    }
}

if (!$stop_info) {
    http_response_code(404);
    $page_title = '404 – Stop Not Found';
    require_once __DIR__ . '/inc/header.php';
    echo '<div class="wrap sec"><p>Stop not found.</p></div>';
    require_once __DIR__ . '/inc/footer.php';
    exit;
}

// ── Build timetable rows for this stop ──────────────────────
$stop_times = [];
foreach ($timetable as $dir_key => $dir) {
    if (!is_array($dir) || empty($dir['trips'])) continue;
    $times = [];
    foreach ($dir['trips'] as $trip) {
        if (isset($trip[$stop_id])) {
            $times[] = $trip[$stop_id];
        }
    }
    if ($times) {
        $r_id = preg_replace('/_return$/', '', $dir_key);
        $route = null;
        foreach ($all_routes as $r) { if ($r['id'] === $r_id) { $route = $r; break; } }
        if ($route) {
            $stop_times[] = [
                'route'   => $route,
                'label'   => $dir['label'],
                'times'   => $times,
            ];
        }
    }
}

// ── Nearby attractions ──────────────────────────────────────
// Match if: stop_id matches exactly, OR stop_id is empty and route_id matches a serving route
$serving_route_ids = array_column(array_column($serving_routes, 'route'), 'id');
$att_route_map  = [];
$att_stop_names = [];
foreach ($all_routes['routes'] ?? [] as $r) {
    $att_route_map[$r['id']] = $r;
    foreach ($r['stops'] as $s) {
        $att_stop_names[$r['id']][$s['id']] = t($s['name']);
    }
}
$nearby_att = array_values(array_filter(
    $att_data['attractions'] ?? [],
    function($a) use ($stop_id, $serving_route_ids) {
        if (empty($a['active'])) return false;
        foreach ($a['nearby'] ?? [] as $nb) {
            if ($nb['stop_id'] === $stop_id) return true; // exact stop match
            if (empty($nb['stop_id']) && in_array($nb['route_id'], $serving_route_ids)) return true; // route-only
        }
        return false;
    }
));
shuffle($nearby_att);

// ── Page meta ───────────────────────────────────────────────
$active_nav = '';
$l = $_SESSION['lang'] ?? ($_GET['lang'] ?? 'th');
$stop_name = $stop_info['name'][$l] ?? $stop_info['name']['en'];
log_event('stop', $stop_name, $stop_id);
$page_title = $stop_name . ' — Phuket Smart Bus Stop';
$page_description = 'Bus stop: ' . $stop_name . '. Routes, timetable and nearby attractions.';
require_once __DIR__ . '/inc/header.php';
?>

<!-- ── Stop Hero ──────────────────────────────────────────── -->
<section class="page-hero" style="padding-bottom:32px">
  <!-- Breadcrumb -->
  <div style="font-size:.8rem;opacity:.7;margin-bottom:10px;text-align:center">
    <a href="<?= base_url() ?>" style="color:inherit">🏠</a> /
    <a href="<?= base_url('timetable.php') ?>" style="color:inherit"><?= $l==='th'?'ตาราง':'Timetable' ?></a> /
    <?= esc($stop_name) ?>
  </div>

  <!-- Stop name — big & prominent -->
  <div style="text-align:center;margin-bottom:6px;font-size:.85rem;opacity:.8;letter-spacing:.5px;text-transform:uppercase;font-weight:600">
    <?= $l==='th'?'ป้ายจอดรถ':'Bus Stop' ?>
  </div>
  <h1 style="text-align:center;margin-bottom:16px;font-size:clamp(1.8rem,5vw,2.8rem);line-height:1.2">
    📍 <?= esc($stop_name) ?>
  </h1>

  <!-- Route badges -->
  <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:24px">
    <?php foreach ($serving_routes as $sr): ?>
    <a href="<?= base_url('timetable.php?route='.$sr['route']['id']) ?>"
       style="background:<?= esc($sr['route']['color']) ?>;color:#fff;border-radius:8px;padding:5px 14px;font-weight:700;font-size:.9rem;text-decoration:none">
      <?= esc($sr['route']['number']) ?> <?= esc(t($sr['route']['name'])) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- ── Route path with current stop highlighted ── -->
  <?php foreach ($serving_routes as $sr): ?>
  <div style="margin-bottom:12px">
    <div style="font-size:.75rem;opacity:.65;text-align:center;margin-bottom:8px;font-weight:600;letter-spacing:.5px">
      <?= esc(t($sr['route']['name'])) ?>
    </div>
    <div class="stop-route-path">
      <?php foreach ($sr['route']['stops'] as $i => $s):
        $is_current = ($s['id'] === $stop_id);
        $s_name = $s['name'][$l] ?? $s['name']['en'];
      ?>
        <?php if ($i > 0): ?>
        <span class="srp-arrow">→</span>
        <?php endif; ?>
        <?php if ($is_current): ?>
        <span class="srp-current" style="background:<?= esc($sr['route']['color']) ?>">
          📍 <?= esc($s_name) ?>
        </span>
        <?php else: ?>
        <a href="<?= base_url('stop.php?id='.$s['id']) ?>" class="srp-stop">
          <?= esc($s_name) ?>
        </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- ── Live tracking buttons ── -->
  <?php
  $trackable = array_filter($serving_routes, fn($sr) => !empty($sr['route']['tracking_url']));
  if ($trackable): ?>
  <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:16px">
    <?php foreach ($trackable as $sr): ?>
    <button onclick="loadTrackFrame('<?= esc($sr['route']['tracking_url']) ?>','<?= esc($sr['route']['id']) ?>')"
            id="btn-<?= esc($sr['route']['id']) ?>"
            style="background:rgba(255,255,255,.15);color:#fff;border:2px solid rgba(255,255,255,.5);border-radius:8px;padding:8px 18px;font-size:.88rem;font-weight:700;cursor:pointer;transition:background .15s">
      📍 <?= $l==='th'?'ติดตามสาย':'Track' ?> <?= esc($sr['route']['number']) ?> <?= $l==='th'?'สด':'Live' ?>
    </button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<!-- ── Tracking iframe (hidden until button clicked) ── -->
<?php if (!empty($trackable)): ?>
<div id="track-frame-wrap" style="display:none;background:#000">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;background:#1a1a2e">
    <span id="track-frame-label" style="color:#fff;font-size:.85rem;font-weight:600"></span>
    <button onclick="closeTrackFrame()" style="background:rgba(255,255,255,.15);color:#fff;border:none;border-radius:6px;padding:4px 12px;cursor:pointer;font-size:.85rem">✕ <?= $l==='th'?'ปิด':'Close' ?></button>
  </div>
  <iframe id="track-frame" src="" allowfullscreen style="width:100%;height:420px;border:none;display:block"></iframe>
</div>
<script>
var _routeNames = <?= json_encode(array_reduce($serving_routes, function($acc, $sr) use ($l) {
  $acc[$sr['route']['id']] = $sr['route']['number'] . ' ' . ($sr['route']['name'][$l] ?? $sr['route']['name']['en']);
  return $acc;
}, []), JSON_UNESCAPED_UNICODE) ?>;
function loadTrackFrame(url, rid) {
  var wrap = document.getElementById('track-frame-wrap');
  document.getElementById('track-frame').src = url;
  document.getElementById('track-frame-label').textContent = '📍 ' + (_routeNames[rid] || rid);
  wrap.style.display = 'block';
  wrap.scrollIntoView({behavior:'smooth', block:'start'});
  // highlight active button
  document.querySelectorAll('[id^="btn-"]').forEach(function(b){ b.style.background='rgba(255,255,255,.15)'; });
  var btn = document.getElementById('btn-' + rid);
  if (btn) btn.style.background = 'rgba(255,255,255,.35)';
}
function closeTrackFrame() {
  document.getElementById('track-frame-wrap').style.display = 'none';
  document.getElementById('track-frame').src = '';
  document.querySelectorAll('[id^="btn-"]').forEach(function(b){ b.style.background='rgba(255,255,255,.15)'; });
}
</script>
<?php endif; ?>

<div class="wrap">

  <!-- ── Stop description (from admin) ────────────────────── -->
  <?php if (!empty($stop_extra['description'][$l]) || !empty($stop_extra['image'])): ?>
  <section class="sec" style="padding-top:28px">
    <?php if (!empty($stop_extra['image'])): ?>
    <div style="border-radius:14px;overflow:hidden;margin-bottom:20px;max-height:280px">
      <img src="<?= esc($stop_extra['image']) ?>" alt="<?= esc($stop_name) ?>" style="width:100%;height:280px;object-fit:cover">
    </div>
    <?php endif; ?>
    <?php if (!empty($stop_extra['description'][$l])): ?>
    <p style="color:var(--muted);line-height:1.7;font-size:.95rem"><?= nl2br(esc($stop_extra['description'][$l])) ?></p>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <!-- ── Timetable ─────────────────────────────────────────── -->
  <?php if ($stop_times): ?>
  <section class="sec" style="padding-top:32px">
    <h2 class="sec-title"><?= $l==='th'?'<span>ตาราง</span>เวลา':'<span>Departure</span> Times' ?></h2>
    <p class="sec-sub" style="margin-bottom:20px"><?= $l==='th'?'เวลาที่รถจอดที่ป้ายนี้':'Bus times at this stop' ?></p>
    <div style="display:flex;flex-direction:column;gap:16px">
      <?php foreach ($stop_times as $st): ?>
      <div style="border:1px solid var(--border);border-radius:12px;overflow:hidden">
        <div style="background:<?= esc($st['route']['color']) ?>;color:#fff;padding:10px 16px;display:flex;align-items:center;gap:10px">
          <span style="background:rgba(255,255,255,.25);border-radius:6px;padding:2px 10px;font-weight:900;font-size:.9rem"><?= esc($st['route']['number']) ?></span>
          <span style="font-weight:600;font-size:.9rem"><?= esc(t($st['label'])) ?></span>
        </div>
        <div style="padding:14px 16px;display:flex;flex-wrap:wrap;gap:8px">
          <?php foreach ($st['times'] as $t_val): ?>
          <span style="background:var(--light);border:1px solid var(--border);border-radius:8px;padding:5px 12px;font-size:.9rem;font-weight:600;font-variant-numeric:tabular-nums">
            🕐 <?= esc($t_val) ?>
          </span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ── Map ───────────────────────────────────────────────── -->
  <?php if (!empty($stop_info['lat']) && !empty($stop_info['lng'])): ?>
  <section class="sec" style="padding-top:32px">
    <h2 class="sec-title"><?= $l==='th'?'<span>ที่ตั้ง</span>ป้ายจอด':'Stop <span>Location</span>' ?></h2>
    <a href="https://maps.google.com/?q=<?= esc($stop_info['lat']) ?>,<?= esc($stop_info['lng']) ?>"
       target="_blank" rel="noopener"
       style="display:inline-flex;align-items:center;gap:8px;background:var(--teal-bg);border:1px solid rgba(1,170,168,.25);color:var(--teal);border-radius:10px;padding:12px 18px;font-weight:600;font-size:.9rem;margin-top:8px;text-decoration:none">
      📍 <?= $l==='th'?'เปิดใน Google Maps':'Open in Google Maps' ?>
      <span style="font-size:.8rem;opacity:.7">(<?= $stop_info['lat'] ?>, <?= $stop_info['lng'] ?>)</span>
    </a>
  </section>
  <?php endif; ?>

  <!-- ── Nearby Attractions ────────────────────────────────── -->
  <?php if ($nearby_att): ?>
  <section class="sec" style="padding-top:32px">
    <h2 class="sec-title"><?= $l==='th'?'<span>สถานที่</span>ใกล้เคียง':'<span>Nearby</span> Attractions' ?></h2>
    <p class="sec-sub" style="margin-bottom:20px"><?= $l==='th'?'สถานที่ท่องเที่ยวที่เดินทางมาได้จากป้ายนี้':'Attractions reachable from this stop' ?></p>
    <div class="att-grid">
      <?php foreach ($nearby_att as $a):
        $a_thumb = !empty($a['images']) ? $a['images'][0] : ($a['image'] ?? '');
      ?>
      <a href="<?= base_url('attraction.php?id='.esc($a['id'])) ?>" class="att-card" style="text-decoration:none;color:inherit">
        <?php if ($a_thumb): ?>
        <div class="att-img"><img src="<?= esc($a_thumb) ?>" alt="<?= esc(t($a['name'])) ?>" loading="lazy"></div>
        <?php else: ?>
        <div class="att-img att-img-ph">🏖️</div>
        <?php endif; ?>
        <div class="att-body">
          <div class="att-title"><?= esc(t($a['name'])) ?></div>
          <div class="att-desc"><?= esc(t($a['description'])) ?></div>
          <?php
            $a_labels = [];
            foreach ($a['nearby'] ?? [] as $nb) {
                $ar = $att_route_map[$nb['route_id']] ?? null;
                if (!$ar) continue;
                $a_labels[] = [
                    'color'      => $ar['color'],
                    'num'        => $ar['number'],
                    'stop'       => $att_stop_names[$nb['route_id']][$nb['stop_id']] ?? '',
                    'route_name' => t($ar['name']),
                ];
            }
          ?>
          <?php if ($a_labels): ?>
          <div class="att-routes">
            <?php foreach ($a_labels as $lb): ?>
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
          <span class="att-map-btn" style="display:inline-block;margin-top:6px"><?= $l==='th'?'ดูรายละเอียด →':'View Details →' ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
