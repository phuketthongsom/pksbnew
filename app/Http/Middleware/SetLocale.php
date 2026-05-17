<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pream's note for the team:
 * URL-prefixed locales for SEO. English at the bare root (/, /blog), other
 * languages get a path prefix (/th/blog, /zh/about, /ru/contact). Google treats
 * each locale as its own indexable page set, and the hreflang tags in the
 * layout point Google at the right URL for each region.
 *
 * Resolution order:
 *   1. URL prefix (set by the {locale} route param) — strongest signal, persistent in URL
 *   2. ?lang= query parameter — quick override (e.g. campaign links)
 *   3. Session — for the user's last choice across pages without locale prefix
 *   4. Accept-Language browser hint — first-time visitors land on their language
 *   5. English fallback
 */
class SetLocale
{
    public const SUPPORTED = ['en', 'th', 'zh', 'ru'];
    public const DEFAULT = 'en';

    public function handle(Request $request, Closure $next): Response
    {
        // Pream's note: URL is the source of truth.
        //   /th/blog → Thai
        //   /blog    → English (the canonical, no exceptions)
        //
        // We do NOT fall back to session here — that would mean visiting /th
        // once "infects" every later request to /, which makes the language
        // picker feel broken (clicking "English" appears to do nothing).
        //
        // Browser language is only honoured on the very first visit to the
        // bare root (`/` with no referrer from this site) — for everything
        // else, the URL wins. ?lang= query is kept as a one-shot override.
        $route = $request->route();
        $localeFromRoute = $route ? $route->parameter('locale') : null;
        $localeFromQuery = $request->query('lang');
        $isCanonicalRoot = $localeFromRoute === null
            && $request->getPathInfo() === '/'
            && !$request->headers->get('referer');

        $locale = $localeFromRoute
            ?? (in_array($localeFromQuery, self::SUPPORTED, true) ? $localeFromQuery : null)
            ?? ($isCanonicalRoot ? $this->detectFromBrowser($request) : null)
            ?? self::DEFAULT;

        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = self::DEFAULT;
        }

        App::setLocale($locale);

        // Drop the {locale} route param so route closures don't have to declare it.
        if ($route && $localeFromRoute !== null) {
            $route->forgetParameter('locale');
        }

        return $next($request);
    }

    protected function detectFromBrowser(Request $request): ?string
    {
        $accept = strtolower((string) $request->header('Accept-Language', ''));
        if (!$accept) return null;
        if (str_starts_with($accept, 'th')) return 'th';
        if (str_starts_with($accept, 'zh')) return 'zh';
        if (str_starts_with($accept, 'ru')) return 'ru';
        if (str_starts_with($accept, 'en')) return 'en';
        return null;
    }
}
