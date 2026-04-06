<?php
$page_title       = 'About Phuket Smart Bus – Premium Public Airport Bus Service';
$active_nav       = 'about';
$page_description = 'About Phuket Smart Bus Co., Ltd. – modern air-conditioned bus service in Phuket, Thailand. Connecting airport, beaches and the city since 2023.';
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
      <p style="line-height:1.85;color:var(--mid);margin-bottom:20px"><?= esc(t($cfg['about_text'])) ?></p>
      <div class="contact-items">
        <div class="c-item"><span class="ico">📞</span><span><?= esc($cfg['phone']) ?></span></div>
        <div class="c-item"><span class="ico">📠</span><span><?= esc($cfg['fax']) ?></span></div>
        <div class="c-item"><span class="ico">✉️</span><a href="mailto:<?= esc($cfg['email']) ?>"><?= esc($cfg['email']) ?></a></div>
        <div class="c-item"><span class="ico">💬</span><a href="https://line.me/ti/p/~<?= esc($cfg['line_id']) ?>" target="_blank" rel="noopener">LINE: <?= esc($cfg['line_id']) ?></a></div>
        <div class="c-item"><span class="ico">📍</span><span><?= esc($cfg['address']) ?></span></div>
      </div>
    </div>
    <div class="about-img">
      <img src="<?= asset('images/hero-tracking.jpg') ?>" alt="Phuket Smart Bus fleet" loading="lazy">
    </div>
  </div>
</div>

<!-- Stats strip -->
<div style="background:linear-gradient(135deg,#0f172a,#1e293b);padding:40px 0">
  <div class="wrap">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:24px;text-align:center">
      <?php
      $stats = [
        ['num'=>'2','lbl'=>['th'=>'เส้นทาง','en'=>'Routes']],
        ['num'=>'30+','lbl'=>['th'=>'ป้ายจอด','en'=>'Bus Stops']],
        ['num'=>'05:00','lbl'=>['th'=>'เวลาเริ่ม','en'=>'First Bus']],
        ['num'=>'00:00','lbl'=>['th'=>'เวลาสิ้นสุด','en'=>'Last Bus']],
        ['num'=>'฿299','lbl'=>['th'=>'ราคาเริ่มต้น','en'=>'From']],
      ];
      foreach ($stats as $s): ?>
      <div>
        <div style="font-size:2rem;font-weight:900;color:var(--yellow);line-height:1.1"><?= $s['num'] ?></div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.65);margin-top:4px;font-weight:600;text-transform:uppercase;letter-spacing:.5px"><?= esc(t($s['lbl'])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Service features -->
<div class="wrap sec">
  <h2 class="sec-title" style="text-align:center"><?= $l==='th'?'ทำไม<span>ต้องเลือกเรา</span>':'Why Choose <span>Us</span>' ?></h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-top:28px">
    <?php
    $features = [
      ['ico'=>'❄️','th'=>'รถปรับอากาศ','en'=>'Air-Conditioned Buses','desc'=>['th'=>'สะดวกสบายตลอดการเดินทาง','en'=>'Comfortable travel throughout your journey']],
      ['ico'=>'📍','th'=>'ติดตามสด','en'=>'Live GPS Tracking','desc'=>['th'=>'ดูตำแหน่งรถแบบ Real-time','en'=>'Real-time bus location on your phone']],
      ['ico'=>'💳','th'=>'Smart Day Pass','en'=>'Smart Day Pass','desc'=>['th'=>'ไม่จำกัดเที่ยว เริ่ม ฿299/วัน','en'=>'Unlimited rides from ฿299/day']],
      ['ico'=>'🛡️','th'=>'ปลอดภัย มีมาตรฐาน','en'=>'Safe & Licensed','desc'=>['th'=>'ได้รับใบอนุญาตประกอบการขนส่ง','en'=>'Fully licensed public transport operator']],
    ];
    foreach ($features as $f): ?>
    <div style="background:var(--light);border-radius:16px;padding:24px 20px;text-align:center">
      <div style="font-size:2.2rem;margin-bottom:10px"><?= $f['ico'] ?></div>
      <div style="font-weight:800;font-size:1rem;color:var(--dark);margin-bottom:6px"><?= esc($l==='th'?$f['th']:$f['en']) ?></div>
      <div style="font-size:.85rem;color:var(--mid);line-height:1.6"><?= esc(t($f['desc'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="teal-strip">
  <h2><?= esc(t($cfg['operating_hours'])) ?></h2>
  <p><?= $l==='th'?'เส้นทางสนามบิน-ราไวย์ และสถานีขนส่ง-ป่าตอง':'Airport–Rawai and Terminal–Patong routes' ?></p>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
