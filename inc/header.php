<?php
// Common header — set $page_title and $active_nav before including this file.
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
auth_start();

if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'th';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['th','en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$cfg        = load_json('config.json');
$l          = lang();
$_pt_base   = $page_title ?? '';
$page_title = $_pt_base !== '' ? $_pt_base . ' — ' . t($cfg['site_name']) : t($cfg['site_name']);
$_lang_params = $_GET; unset($_lang_params['lang']);
$_lang_params['lang'] = ($l==='th' ? 'en' : 'th');
$lang_url   = '?' . http_build_query($_lang_params);
$lang_lbl   = $l==='th' ? 'EN' : 'ไทย';

// ── SEO variables ──────────────────────────────────────────────────────────────
$_desc_default = ['th'=>'ภูเก็ต สมาร์ท บัส บริการรถโดยสารสาธารณะคุณภาพสูง เชื่อมต่อสนามบินภูเก็ต ราไวย์ ป่าตอง ดูตำแหน่งรถสดและตาราง','en'=>'Phuket Smart Bus – premium public bus connecting Phuket Airport to Rawai and Patong. Live tracking, timetable & Smart Day Pass from ฿299.'];
$_kw_default   = ['th'=>'ภูเก็ต สมาร์ท บัส,รถโดยสารภูเก็ต,สนามบินภูเก็ต,ราไวย์,ป่าตอง,สมาร์ท เดย์ พาส','en'=>'Phuket Smart Bus,Phuket airport bus,bus Phuket to Rawai,bus Phuket to Patong,Smart Day Pass Phuket'];
$_page_desc    = isset($page_description) ? $page_description : t($_desc_default);
$_page_kw      = isset($page_keywords)    ? $page_keywords    : t($_kw_default);

// Canonical & hreflang URLs (strip lang query param)
$_qs_extra  = $_GET; unset($_qs_extra['lang']);
$_page_path = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$_canonical = base_url($_page_path) . ($_qs_extra ? '?' . http_build_query($_qs_extra) : '');
$_sep       = $_qs_extra ? '&' : '?';
$_url_th    = $_canonical . $_sep . 'lang=th';
$_url_en    = $_canonical . $_sep . 'lang=en';
$_og_image  = asset('images/hero-home.jpg');
$_og_locale = $l === 'th' ? 'th_TH' : 'en_US';
$_og_locale_alt = $l === 'th' ? 'en_US' : 'th_TH';

// JSON-LD LocalBusiness
$_jsonld = json_encode([
  '@context'    => 'https://schema.org',
  '@type'       => 'LocalBusiness',
  '@id'         => base_url(),
  'name'        => 'Phuket Smart Bus',
  'alternateName' => 'ภูเก็ต สมาร์ท บัส',
  'description' => 'Premium public bus service connecting Phuket International Airport with Rawai Beach and Patong via modern air-conditioned buses.',
  'url'         => base_url(),
  'telephone'   => $cfg['phone'],
  'email'       => $cfg['email'],
  'address'     => ['@type'=>'PostalAddress','streetAddress'=>$cfg['address'],'addressLocality'=>'Phuket','addressCountry'=>'TH','postalCode'=>'83000'],
  'geo'         => ['@type'=>'GeoCoordinates','latitude'=>7.869,'longitude'=>98.394],
  'image'       => $_og_image,
  'sameAs'      => [$cfg['facebook'] ?? ''],
  'openingHours'=> 'Mo-Su 06:00-20:00',
  'priceRange'  => '฿299-฿1,990',
  'currenciesAccepted' => 'THB',
  'paymentAccepted'    => 'Cash, Credit Card, QR Code',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$nav = [
  'home'        => ['url'=>base_url(),                    'th'=>'หน้าแรก',          'en'=>'Home'],
  'tracking'    => ['url'=>base_url('tracking.php'),      'th'=>'ตำแหน่งรถ',       'en'=>'Tracking'],
  'timetable'   => ['url'=>base_url('timetable.php'),     'th'=>'เวลาเดินรถ',       'en'=>'Timetable'],
  'attractions' => ['url'=>base_url('attractions.php'),   'th'=>'สถานที่ท่องเที่ยว','en'=>'Attractions'],
  'payment'     => ['url'=>base_url('payment.php'),       'th'=>'บัตรโดยสาร',      'en'=>'Passes'],
  'about'       => ['url'=>base_url('about.php'),         'th'=>'เกี่ยวกับ',        'en'=>'About'],
  'contact'     => ['url'=>base_url('contact.php'),       'th'=>'ติดต่อ',           'en'=>'Contact'],
];
$cur = $active_nav ?? '';
?>
<!DOCTYPE html>
<html lang="<?= $l ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= esc($page_title) ?></title>
  <meta name="description" content="<?= esc($_page_desc) ?>">
  <meta name="keywords"    content="<?= esc($_page_kw) ?>">
  <meta name="robots"      content="index, follow">
  <meta name="theme-color" content="#01aaa8">

  <!-- Canonical & hreflang -->
  <link rel="canonical"  href="<?= esc($_canonical) ?>">
  <link rel="alternate"  hreflang="th"        href="<?= esc($_url_th) ?>">
  <link rel="alternate"  hreflang="en"        href="<?= esc($_url_en) ?>">
  <link rel="alternate"  hreflang="x-default" href="<?= esc($_canonical) ?>">

  <!-- Open Graph -->
  <meta property="og:type"             content="website">
  <meta property="og:site_name"        content="Phuket Smart Bus">
  <meta property="og:title"            content="<?= esc($page_title) ?>">
  <meta property="og:description"      content="<?= esc($_page_desc) ?>">
  <meta property="og:url"              content="<?= esc($_canonical) ?>">
  <meta property="og:image"            content="<?= esc($_og_image) ?>">
  <meta property="og:image:width"      content="1200">
  <meta property="og:image:height"     content="630">
  <meta property="og:image:alt"        content="Phuket Smart Bus">
  <meta property="og:locale"           content="<?= $_og_locale ?>">
  <meta property="og:locale:alternate" content="<?= $_og_locale_alt ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= esc($page_title) ?>">
  <meta name="twitter:description" content="<?= esc($_page_desc) ?>">
  <meta name="twitter:image"       content="<?= esc($_og_image) ?>">

  <!-- Structured Data -->
  <script type="application/ld+json"><?= $_jsonld ?></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800;900&display=swap" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800;900&display=swap"></noscript>
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="<?= ($active_nav??'')==='home' ? 'page-home' : '' ?>">

<?php if (!empty($cfg['announcement']['active']) && !empty($cfg['announcement']['text'][$l])): ?>
<div class="ann-bar"><?= esc(t($cfg['announcement']['text'])) ?></div>
<?php endif; ?>

<header class="site-header">
  <a href="<?= base_url() ?>" class="header-logo">
    <img src="<?= asset('images/logo.png') ?>" alt="Phuket Smart Bus" width="110" height="36">
  </a>

  <!-- Desktop nav -->
  <nav class="main-nav">
    <a href="<?= $nav['home']['url'] ?>"        class="<?= $cur==='home'?'active':'' ?>"><?= $nav['home'][$l] ?></a>
    <span class="nav-sep"></span>
    <a href="<?= $nav['tracking']['url'] ?>"    class="<?= $cur==='tracking'?'active':'' ?>"><?= $nav['tracking'][$l] ?></a>
    <a href="<?= $nav['timetable']['url'] ?>"   class="<?= $cur==='timetable'?'active':'' ?>"><?= $nav['timetable'][$l] ?></a>
    <a href="<?= $nav['attractions']['url'] ?>" class="<?= $cur==='attractions'?'active':'' ?>"><?= $nav['attractions'][$l] ?></a>
    <span class="nav-sep"></span>
    <a href="<?= $nav['payment']['url'] ?>"     class="<?= $cur==='payment'?'active':'' ?>"><?= $nav['payment'][$l] ?></a>
    <span class="nav-sep"></span>
    <a href="<?= $nav['about']['url'] ?>"       class="<?= $cur==='about'?'active':'' ?>"><?= $nav['about'][$l] ?></a>
    <a href="<?= $nav['contact']['url'] ?>"     class="<?= $cur==='contact'?'active':'' ?>"><?= $nav['contact'][$l] ?></a>
    <a href="<?= $lang_url ?>" class="lang-btn"><?= $lang_lbl ?></a>
  </nav>

  <!-- Mobile: lang toggle only -->
  <div class="header-lang">
    <a href="<?= $lang_url ?>"><?= $lang_lbl ?></a>
  </div>
</header>

<!-- Mobile bottom nav -->
<nav class="bottom-nav">
  <a href="<?= base_url() ?>" class="<?= $cur==='home'?'active':'' ?>">
    <span class="ico">🏠</span><span><?= $nav['home'][$l] ?></span>
  </a>
  <a href="<?= base_url('tracking.php') ?>" class="<?= $cur==='tracking'?'active':'' ?>">
    <span class="ico">📍</span><span><?= $nav['tracking'][$l] ?></span>
  </a>
  <a href="<?= base_url('timetable.php') ?>" class="<?= $cur==='timetable'?'active':'' ?>">
    <span class="ico">🕐</span><span><?= $nav['timetable'][$l] ?></span>
  </a>
  <button class="bn-more-btn <?= in_array($cur,['attractions','payment','about','contact'])?'active':'' ?>" onclick="document.getElementById('bn-more').classList.toggle('open')">
    <span class="ico">☰</span><span><?= $l==='th'?'อื่นๆ':'More' ?></span>
  </button>
</nav>

<!-- More menu popup -->
<div id="bn-more" class="bn-more-panel">
  <div class="bn-more-inner">
    <a href="<?= base_url('attractions.php') ?>" class="<?= $cur==='attractions'?'active':'' ?>">
      <span>🗺️</span><?= $nav['attractions'][$l] ?>
    </a>
    <a href="<?= base_url('payment.php') ?>" class="<?= $cur==='payment'?'active':'' ?>">
      <span>💳</span><?= $nav['payment'][$l] ?>
    </a>
    <a href="<?= base_url('about.php') ?>" class="<?= $cur==='about'?'active':'' ?>">
      <span>ℹ️</span><?= $nav['about'][$l] ?>
    </a>
    <a href="<?= base_url('contact.php') ?>" class="<?= $cur==='contact'?'active':'' ?>">
      <span>✉️</span><?= $nav['contact'][$l] ?>
    </a>
    <a href="<?= $lang_url ?>" class="bn-more-lang">
      🌐 <?= $lang_lbl ?>
    </a>
  </div>
</div>
<div class="bn-more-overlay" onclick="document.getElementById('bn-more').classList.remove('open')"></div>
