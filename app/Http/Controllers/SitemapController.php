<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Services\PostsRepository;
use Illuminate\Http\Request;

/**
 * Pream's note for the team:
 * One <url> per locale per page, with xhtml:link alternates and x-default.
 * Blog posts include real lastmod from published_at/updated_at and
 * image:image entries so Google can index cover photos via the image sitemap.
 */
class SitemapController extends Controller
{
    public function __invoke(Request $request, PostsRepository $repo)
    {
        $base    = rtrim($request->getSchemeAndHttpHost(), '/');
        $today   = date('Y-m-d');
        $locales = SetLocale::SUPPORTED;

        // Static pages: [path, priority, changefreq, lastmod]
        $paths = [
            ['', '1.0', 'weekly', $today],
            ['/timetable', '0.9', 'weekly',  $today],
            ['/tracking',  '0.9', 'daily',   $today],
            ['/pass',      '0.9', 'monthly', $today],
            ['/payment',   '0.7', 'monthly', $today],
            ['/blog',      '0.8', 'weekly',  $today],
            ['/about',     '0.6', 'monthly', $today],
            ['/contact',   '0.6', 'monthly', $today],
        ];

        // Blog posts with real dates and cover images
        $posts    = $repo->all();
        $postMeta = [];
        foreach ($posts as $post) {
            $slug    = $post['slug'];
            $lastmod = substr($post['updated_at'] ?? $post['published_at'] ?? $today, 0, 10);
            $paths[] = ['/blog/'.$slug, '0.7', 'monthly', $lastmod];
            // store cover for image sitemap entries
            if (!empty($post['cover'])) {
                $postMeta[$slug] = [
                    'image' => $base.'/'.$post['cover'],
                    'title' => $post['title'] ?? '',
                ];
            }
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml"'."\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

        foreach ($paths as [$path, $priority, $changefreq, $lastmod]) {
            // Parse slug from path for image lookup
            $slug = ltrim($path, '/blog/');
            $isBlogPost = str_starts_with($path, '/blog/') && strlen($path) > 6;

            foreach ($locales as $locale) {
                $localePrefix = $locale === 'en' ? '' : '/'.$locale;
                $xml .= "  <url>\n";
                $xml .= "    <loc>{$base}{$localePrefix}{$path}</loc>\n";
                foreach ($locales as $alt) {
                    $altPrefix = $alt === 'en' ? '' : '/'.$alt;
                    $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"{$alt}\" href=\"{$base}{$altPrefix}{$path}\"/>\n";
                }
                $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"{$base}{$path}\"/>\n";
                $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
                $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
                $xml .= "    <priority>{$priority}</priority>\n";

                // Add image entry for blog posts
                if ($isBlogPost && isset($postMeta[$slug])) {
                    $imgUrl   = htmlspecialchars($postMeta[$slug]['image']);
                    $imgTitle = htmlspecialchars($postMeta[$slug]['title']);
                    $xml .= "    <image:image>\n";
                    $xml .= "      <image:loc>{$imgUrl}</image:loc>\n";
                    $xml .= "      <image:title>{$imgTitle}</image:title>\n";
                    $xml .= "    </image:image>\n";
                }

                $xml .= "  </url>\n";
            }
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
