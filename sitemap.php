<?php
require_once __DIR__ . '/inc/helpers.php';

header('Content-Type: application/xml; charset=utf-8');

$base   = rtrim(base_url(), '/');
$today  = date('Y-m-d');

$routes      = array_values(array_filter(load_json('routes.json'), fn($r) => !empty($r['active'])));
$att_data    = load_json('attractions.json');
$attractions = array_filter($att_data['attractions'] ?? [], fn($a) => !empty($a['active']));

// Static pages: url, lastmod, changefreq, priority
$static = [
    ['',              $today, 'daily',   '1.0'],
    ['tracking.php',  $today, 'daily',   '0.9'],
    ['timetable.php', $today, 'weekly',  '0.9'],
    ['attractions.php',$today,'weekly',  '0.8'],
    ['payment.php',   $today, 'monthly', '0.8'],
    ['about.php',     $today, 'monthly', '0.6'],
    ['contact.php',   $today, 'monthly', '0.6'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

foreach ($static as [$path, $lm, $cf, $pri]) {
    $url = $base . '/' . ltrim($path, '/');
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($url) . "</loc>\n";
    echo "    <lastmod>{$lm}</lastmod>\n";
    echo "    <changefreq>{$cf}</changefreq>\n";
    echo "    <priority>{$pri}</priority>\n";
    echo "    <xhtml:link rel=\"alternate\" hreflang=\"th\" href=\"" . htmlspecialchars($url . '?lang=th') . "\"/>\n";
    echo "    <xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"" . htmlspecialchars($url . '?lang=en') . "\"/>\n";
    echo "  </url>\n";
}

// Stop pages
foreach ($routes as $r) {
    foreach ($r['stops'] ?? [] as $s) {
        $url = $base . '/stop.php?id=' . urlencode($s['id']);
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        echo "    <lastmod>{$today}</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.6</priority>\n";
        echo "  </url>\n";
    }
}

// Attraction pages
foreach ($attractions as $a) {
    $url = $base . '/attraction.php?id=' . urlencode($a['id']);
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($url) . "</loc>\n";
    echo "    <lastmod>{$today}</lastmod>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.7</priority>\n";
    echo "    <xhtml:link rel=\"alternate\" hreflang=\"th\" href=\"" . htmlspecialchars($url . '&lang=th') . "\"/>\n";
    echo "    <xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"" . htmlspecialchars($url . '&lang=en') . "\"/>\n";
    echo "  </url>\n";
}

echo '</urlset>';
