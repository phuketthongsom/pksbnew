<?php
// Shared pass card partial.
// Requires: $passes (array from passes.json), $routes (filtered active routes), $l (lang)
foreach ($passes['passes'] as $p):
  $color = esc($p['color']);
  // Show pass-specific routes if set, otherwise all active routes
  $pass_routes    = !empty($p['routes']) ? $p['routes'] : [];
  $display_routes = empty($pass_routes)
    ? $routes
    : array_filter($routes, fn($r) => in_array($r['id'], $pass_routes));
  preg_match('/(\d+)/', t($p['name']), $m);
?>
<a href="<?= base_url('pass.php') ?>?id=<?= esc($p['id']) ?><?= $l==='en'?'&lang=en':'' ?>" class="pass-card-link">
<div class="pass-card">
  <!-- Coloured top face -->
  <div class="pass-card-top" style="background:linear-gradient(135deg,<?= $color ?> 0%,<?= $color ?>cc 100%)">
    <div>
      <div class="pass-card-brand">Smart Day Pass</div>
      <div class="pass-card-dur">
        <?= $m[1] ?? esc(t($p['name'])) ?>
        <span><?= strpos(t($p['name']),'30')!==false ? ($l==='th'?'วัน':'days') : ($l==='th'?'วัน':'day'.(($m[1]??1)>1?'s':'')) ?></span>
      </div>
    </div>
    <div class="pass-card-routes">
      <?php foreach ($display_routes as $r): ?>
      <span class="pass-card-rn"><?= esc($r['number']) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
  <!-- White bottom face -->
  <div class="pass-card-bot">
    <div>
      <div class="pass-card-price" style="color:<?= $color ?>">
        <sup>฿</sup><?= number_format($p['price']) ?>
      </div>
      <div class="pass-card-validity"><?= esc(t($p['validity'])) ?></div>
    </div>
    <span class="btn btn-teal btn-sm" style="background:<?= $color ?>;border-color:<?= $color ?>">
      <?= $l==='th'?'ดูเพิ่มเติม':'Details' ?>
    </span>
  </div>
</div>
</a>
<?php endforeach; ?>
