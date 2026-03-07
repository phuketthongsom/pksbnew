<?php
$page_title       = 'เกี่ยวกับเรา / About Us';
$active_nav       = 'about';
$page_description = 'About Phuket Smart Bus Co., Ltd. – modern air-conditioned bus service in Phuket, Thailand. Connecting airport, beaches and the city since day one. | เกี่ยวกับบริษัท ภูเก็ต สมาร์ท บัส จำกัด บริการรถโดยสารสาธารณะคุณภาพสูงในภูเก็ต';
$page_keywords    = 'Phuket Smart Bus company,about Phuket Smart Bus,public bus Phuket Thailand,บริษัท ภูเก็ต สมาร์ท บัส,รถโดยสารสาธารณะภูเก็ต';
require_once __DIR__ . '/inc/header.php';
?>

<!-- ── Page Hero -->
<div class="page-hero">
  <h1><?= $l==='th'?'เกี่ยวกับเรา':'About Us' ?></h1>
  <p><?= $l==='th'?'บริษัท ภูเก็ต สมาร์ท บัส จำกัด':'Phuket Smart Bus Co., Ltd.' ?></p>
</div>

<div class="wrap sec">
  <div class="about-grid">
    <div>
      <h2 class="sec-title"><?= $l==='th'?'บริษัทของ<span>เรา</span>':'Our <span>Company</span>' ?></h2>
      <p style="line-height:1.85;color:#555;margin-bottom:20px"><?= esc(t($cfg['about_text'])) ?></p>
      <div class="contact-items">
        <div class="c-item"><span class="ico">📞</span><span><?= esc($cfg['phone']) ?></span></div>
        <div class="c-item"><span class="ico">📠</span><span><?= esc($cfg['fax']) ?></span></div>
        <div class="c-item"><span class="ico">✉️</span><a href="mailto:<?= esc($cfg['email']) ?>"><?= esc($cfg['email']) ?></a></div>
        <div class="c-item"><span class="ico">💬</span><span>LINE: <?= esc($cfg['line_id']) ?></span></div>
        <div class="c-item"><span class="ico">📍</span><span><?= esc($cfg['address']) ?></span></div>
      </div>
    </div>
    <div class="about-img">
      <img src="<?= asset('images/hero-tracking.jpg') ?>" alt="Phuket Smart Bus" loading="lazy">
    </div>
  </div>
</div>

<div class="teal-strip">
  <h2><?= esc(t($cfg['operating_hours'])) ?></h2>
  <p><?= $l==='th'?'เส้นทางสนามบิน-ราไวย์ และสถานีขนส่ง-ป่าตอง':'Airport–Rawai and Terminal–Patong routes' ?></p>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
