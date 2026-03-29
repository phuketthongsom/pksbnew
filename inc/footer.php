<?php $cfg = $cfg ?? load_json('config.json'); $l = lang(); ?>
</main>
<footer class="site-footer">
  <div class="footer-grid">
    <div class="footer-col">
      <div class="footer-logo"><img src="<?= asset('images/logo.png') ?>" alt="Phuket Smart Bus" loading="lazy"></div>
      <p><?= esc($cfg['company_name']) ?></p>
      <p><?= esc($cfg['address']) ?></p>
    </div>
    <div class="footer-col">
      <h4><?= $l==='th'?'ติดต่อเรา':'Contact' ?></h4>
      <p>📞 <?= esc($cfg['phone']) ?></p>
      <a href="mailto:<?= esc($cfg['email']) ?>">✉️ <?= esc($cfg['email']) ?></a>
      <p>💬 LINE: <?= esc($cfg['line_id']) ?></p>
    </div>
    <div class="footer-col">
      <h4><?= $l==='th'?'เมนู':'Menu' ?></h4>
      <a href="<?= base_url('tracking.php') ?>"><?= $l==='th'?'ตำแหน่งรถ':'Tracking' ?></a>
      <a href="<?= base_url('timetable.php') ?>"><?= $l==='th'?'เวลาเดินรถ':'Timetable' ?></a>
      <a href="<?= base_url('attractions.php') ?>"><?= $l==='th'?'สถานที่ใกล้เคียง':'Nearby Places' ?></a>
      <a href="<?= base_url('payment.php') ?>"><?= $l==='th'?'บัตรโดยสาร':'Passes' ?></a>
      <a href="<?= base_url('about.php') ?>"><?= $l==='th'?'เกี่ยวกับเรา':'About' ?></a>
      <a href="<?= base_url('contact.php') ?>"><?= $l==='th'?'ติดต่อ':'Contact' ?></a>
    </div>
    <div class="footer-col">
      <h4><?= $l==='th'?'เวลาให้บริการ':'Hours' ?></h4>
      <p><?= esc(t($cfg['operating_hours'])) ?></p>
      <?php if (!empty($cfg['facebook'])): ?>
      <a href="<?= esc($cfg['facebook']) ?>" target="_blank" rel="noopener">📘 Facebook</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="footer-bottom">© <?= date('Y') ?> <?= esc($cfg['company_name']) ?></div>
</footer>
<script src="<?= asset('js/main.js') ?>" defer></script>

<?php if (!empty($cfg['facebook_page_id'])): ?>
<!-- ── Facebook Messenger Chat Plugin ──────────────────────── -->
<div id="fb-root"></div>
<div class="fb-customerchat"
  attribution="biz_inbox"
  page_id="<?= esc($cfg['facebook_page_id']) ?>"
  theme_color="#01aaa8"
  logged_in_greeting="<?= $l==='th' ? 'สวัสดีครับ! มีอะไรให้ช่วยได้บ้าง?' : 'Hi! How can we help you?' ?>"
  logged_out_greeting="<?= $l==='th' ? 'สวัสดีครับ! มีอะไรให้ช่วยได้บ้าง?' : 'Hi! How can we help you?' ?>">
</div>
<script>
window.fbAsyncInit = function() {
  FB.init({ xfbml: true, version: 'v20.0' });
};
(function(d,s,id){
  var js,fjs=d.getElementsByTagName(s)[0];
  if(d.getElementById(id))return;
  js=d.createElement(s);js.id=id;
  js.src='https://connect.facebook.net/<?= $l==='th'?'th_TH':'en_US' ?>/sdk/xfbml.customerchat.js';
  fjs.parentNode.insertBefore(js,fjs);
}(document,'script','facebook-jssdk'));
</script>
<?php endif; ?>
</body>
</html>
