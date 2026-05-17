<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Pream's note for the team:
 * Single chokepoint for any HTML written by an admin/translator. Quill output
 * lands here on save (and so does anything pasted into the HTML-mode tab).
 * Without this, a translator could paste `<script>` and steal cookies on
 * every visitor — see SECURITY.md (audit B1).
 *
 * Whitelist is intentionally narrow. If a destination guide needs a new tag,
 * add it here, not via a route-level bypass.
 */
class HtmlSanitizer
{
    protected HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();

        $config->set('Cache.SerializerPath', storage_path('framework/htmlpurifier'));
        @mkdir(storage_path('framework/htmlpurifier'), 0775, true);

        // Allowed tags + attributes — whitelist, not blacklist.
        // (figure/figcaption omitted: HTMLPurifier ships HTML4 strict by
        // default and HTML5 figure support requires the html5lib extension —
        // not worth the dep for our content shape.)
        $config->set('HTML.Allowed', implode(',', [
            'p', 'br',
            'h2', 'h3', 'h4',
            'ul', 'ol', 'li',
            'strong', 'em', 'u',
            'a[href|title|rel|target]',
            'blockquote',
            'img[src|alt|width|height]',
            'span[class]',
        ]));

        // Only http / https / mailto on links — kills javascript: and data: URIs.
        $config->set('URI.AllowedSchemes', [
            'http' => true, 'https' => true, 'mailto' => true,
        ]);
        // Force any external link to open safely.
        $config->set('HTML.TargetBlank', false);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.Nofollow', false);

        // Drop empty paragraphs Quill loves to insert.
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);

        $this->purifier = new HTMLPurifier($config);
    }

    /** Clean a single string of (potentially) untrusted HTML. */
    public function clean(?string $html): string
    {
        if ($html === null || $html === '') return '';
        return $this->purifier->purify($html);
    }

    /**
     * Run an array of translations through the sanitizer, only on the body
     * field (other fields are plain text, so they go through Blade's default
     * `{{ }}` escaping at render time).
     */
    public function cleanTranslationBodies(array $translations): array
    {
        foreach ($translations as $locale => &$tx) {
            if (!is_array($tx)) continue;
            if (isset($tx['body'])) {
                $tx['body'] = $this->clean($tx['body']);
            }
        }
        return $translations;
    }
}
