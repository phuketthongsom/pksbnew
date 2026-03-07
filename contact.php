<?php
$page_title       = 'ติดต่อเรา / Contact';
$active_nav       = 'contact';
$page_description = 'Contact Phuket Smart Bus – Phone: 086-306-1257, Email: info@phuketsmartbus.com. Office in Wichit, Phuket 83000. Open daily 06:00–20:00. | ติดต่อ ภูเก็ต สมาร์ท บัส โทร 086-306-1257';
$page_keywords    = 'Phuket Smart Bus contact,Phuket bus phone number,ติดต่อภูเก็ต สมาร์ท บัส,เบอร์โทรรถโดยสารภูเก็ต';
require_once __DIR__ . '/inc/header.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email && $message) {
        $success = $l==='th'
            ? 'ส่งข้อความเรียบร้อยแล้ว เราจะติดต่อกลับโดยเร็ว!'
            : 'Message sent! We will get back to you soon.';
    } else {
        $error = $l==='th' ? 'กรุณากรอกข้อมูลให้ครบทุกช่อง' : 'Please fill in all fields.';
    }
}
?>

<!-- ── Page Hero -->
<div class="page-hero">
  <h1><?= $l==='th'?'ติดต่อเรา':'Contact Us' ?></h1>
  <p><?= $l==='th'?"เราพร้อมตอบทุกคำถาม":"We're here to answer any questions" ?></p>
</div>

<div class="wrap sec">
  <?php if ($success): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-error"><?= esc($error) ?></div><?php endif; ?>

  <div class="contact-grid">
    <!-- Info -->
    <div>
      <h2 class="sec-title"><?= $l==='th'?'ข้อมูล<span>ติดต่อ</span>':'Get In <span>Touch</span>' ?></h2>
      <div class="contact-info-list" style="margin-top:16px">
        <div class="ci"><span class="lbl"><?= $l==='th'?'บริษัท:':'Company:' ?></span><span><?= esc($cfg['company_name']) ?></span></div>
        <div class="ci"><span class="lbl"><?= $l==='th'?'ที่อยู่:':'Address:' ?></span><span><?= esc($cfg['address']) ?></span></div>
        <div class="ci"><span class="lbl"><?= $l==='th'?'โทร:':'Phone:' ?></span><span><?= esc($cfg['phone']) ?></span></div>
        <div class="ci"><span class="lbl"><?= $l==='th'?'แฟกซ์:':'Fax:' ?></span><span><?= esc($cfg['fax']) ?></span></div>
        <div class="ci"><span class="lbl">Email:</span><a href="mailto:<?= esc($cfg['email']) ?>"><?= esc($cfg['email']) ?></a></div>
        <div class="ci"><span class="lbl">LINE:</span><span><?= esc($cfg['line_id']) ?></span></div>
        <div class="ci"><span class="lbl"><?= $l==='th'?'เวลา:':'Hours:' ?></span><span><?= esc(t($cfg['operating_hours'])) ?></span></div>
      </div>
      <div class="map-embed" style="margin-top:20px">
        <iframe
          src="https://www.openstreetmap.org/export/embed.html?bbox=98.36,7.85,98.44,7.90&layer=mapnik&marker=7.869,98.394"
          loading="lazy" allowfullscreen>
        </iframe>
      </div>
    </div>

    <!-- Form -->
    <div>
      <h2 class="sec-title"><?= $l==='th'?'ส่ง<span>ข้อความ</span>':'Send <span>Message</span>' ?></h2>
      <form method="post" style="margin-top:16px">
        <div class="form-group">
          <label><?= $l==='th'?'ชื่อ-นามสกุล':'Full Name' ?></label>
          <input type="text" name="name" required value="<?= esc($_POST['name']??'') ?>" placeholder="<?= $l==='th'?'กรอกชื่อของคุณ':'Your name' ?>">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required value="<?= esc($_POST['email']??'') ?>" placeholder="your@email.com">
        </div>
        <div class="form-group">
          <label><?= $l==='th'?'หัวข้อ':'Subject' ?></label>
          <input type="text" name="subject" value="<?= esc($_POST['subject']??'') ?>" placeholder="<?= $l==='th'?'หัวข้อข้อความ':'Message subject' ?>">
        </div>
        <div class="form-group">
          <label><?= $l==='th'?'ข้อความ':'Message' ?></label>
          <textarea name="message" required placeholder="<?= $l==='th'?'พิมพ์ข้อความของคุณที่นี่':'Type your message here' ?>"><?= esc($_POST['message']??'') ?></textarea>
        </div>
        <button type="submit" class="btn btn-teal"><?= $l==='th'?'ส่งข้อความ':'Send Message' ?></button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
