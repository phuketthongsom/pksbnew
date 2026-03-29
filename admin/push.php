<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();

$admin_page_title = 'Push Notifications';
$admin_active_nav = 'push';

$subs  = load_json('push_subscriptions.json');
$count = count($subs['subscriptions'] ?? []);
?>
<?php require_once __DIR__ . '/inc/admin_header.php'; ?>

<div class="admin-card">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h2>🔔 Push Notifications</h2>
    <span style="background:rgba(1,170,168,.15);color:var(--teal);border-radius:8px;padding:5px 14px;font-size:.88rem;font-weight:700">
      <?= $count ?> subscriber<?= $count !== 1 ? 's' : '' ?>
    </span>
  </div>

  <p style="color:#888;margin-bottom:24px;font-size:.9rem">
    Send a push notification to all users who have enabled notifications on their device.
  </p>

  <div id="send-result" style="display:none;margin-bottom:16px"></div>

  <form id="push-form">
    <div class="form-group">
      <label>Title *</label>
      <input type="text" id="p-title" placeholder="e.g. 🚌 Service Update" maxlength="80" required>
    </div>
    <div class="form-group">
      <label>Message *</label>
      <textarea id="p-body" rows="3" placeholder="e.g. Route 8357 is now running on time." maxlength="200" required></textarea>
    </div>
    <div class="form-group">
      <label>Link URL <small style="color:#aaa">(optional — opens when notification tapped)</small></label>
      <input type="text" id="p-url" placeholder="e.g. /timetable.php" value="/">
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
      <button type="submit" class="btn btn-primary" id="send-btn">
        📤 Send to <?= $count ?> subscriber<?= $count !== 1 ? 's' : '' ?>
      </button>
      <!-- Quick templates -->
      <button type="button" class="btn btn-sm" onclick="fillTemplate('delay')">🚌 Delay</button>
      <button type="button" class="btn btn-sm" onclick="fillTemplate('resume')">✅ Resume</button>
      <button type="button" class="btn btn-sm" onclick="fillTemplate('promo')">🎟️ Promo</button>
    </div>
  </form>
</div>

<div class="admin-card" style="margin-top:20px">
  <h3 style="margin-bottom:12px">Subscriber Devices</h3>
  <?php if ($count): ?>
  <p style="color:#888;font-size:.88rem"><?= $count ?> device<?= $count !== 1 ? 's' : '' ?> subscribed. Subscriptions are automatically removed when they expire or the user unsubscribes.</p>
  <?php else: ?>
  <p style="color:#888;font-size:.88rem">No subscribers yet. Users who visit the site and accept the notification prompt will appear here.</p>
  <?php endif; ?>
</div>

<script>
function fillTemplate(t) {
  const templates = {
    delay: {
      title: '🚌 แจ้งเตือนรถล่าช้า',
      body: 'รถสาย 8357 อาจมีความล่าช้าในช่วงนี้ กรุณาตรวจสอบเวลาออกรถ',
      url: '/timetable.php'
    },
    resume: {
      title: '✅ รถให้บริการตามปกติ',
      body: 'ทุกสายกลับมาให้บริการตามตารางเวลาปกติแล้ว',
      url: '/timetable.php'
    },
    promo: {
      title: '🎟️ โปรโมชั่นพิเศษ!',
      body: 'ซื้อบัตรโดยสาร Smart Day Pass วันนี้ ราคาพิเศษสำหรับนักท่องเที่ยว',
      url: '/payment.php'
    }
  };
  const d = templates[t];
  document.getElementById('p-title').value = d.title;
  document.getElementById('p-body').value  = d.body;
  document.getElementById('p-url').value   = d.url;
}

document.getElementById('push-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('send-btn');
  btn.disabled = true;
  btn.textContent = 'Sending…';

  const fd = new FormData();
  fd.append('title', document.getElementById('p-title').value);
  fd.append('body',  document.getElementById('p-body').value);
  fd.append('url',   document.getElementById('p-url').value);

  try {
    const r = await fetch('../api/push_send.php', { method:'POST', body:fd });
    const d = await r.json();
    const el = document.getElementById('send-result');
    el.style.display = 'block';
    if (d.sent !== undefined) {
      el.innerHTML = `<div class="alert-success">✅ Sent to ${d.sent} device${d.sent!==1?'s':''}.${d.failed?' ('+d.failed+' expired removed)':''}</div>`;
    } else {
      el.innerHTML = `<div class="alert-error">❌ ${d.error || 'Unknown error'}</div>`;
    }
  } catch {
    document.getElementById('send-result').innerHTML = '<div class="alert-error">❌ Request failed</div>';
    document.getElementById('send-result').style.display = 'block';
  }

  btn.disabled = false;
  btn.textContent = '📤 Send Notification';
});
</script>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>
