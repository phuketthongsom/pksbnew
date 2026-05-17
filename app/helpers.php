<?php

/*
 * Pream's note for the team:
 * lurl() is the locale-aware sibling of route(). Use it for any PUBLIC link
 * (header nav, footer, hero CTAs, blog cards, etc.) — it preserves the user's
 * current locale by prepending /th, /zh, or /ru to the generated path.
 *
 * For ADMIN links keep using route() so /admin/posts never picks up a /th
 * prefix.
 *
 * Why a helper instead of overriding route(): keeping route() pristine lets us
 * generate canonical (non-locale) URLs for the sitemap and for hreflang
 * computation in the layout. Two helpers, one job each.
 */
if (!function_exists('current_admin')) {
    /**
     * Return the currently-authenticated admin user (array) or null.
     * Cheap accessor so route closures and Blade can ask the same question.
     */
    function current_admin(): ?array
    {
        $req = request();
        return $req ? $req->attributes->get('admin_user') : null;
    }
}

if (!function_exists('current_admin_can')) {
    function current_admin_can(string $permission): bool
    {
        return \App\Services\UserRepository::userCan(current_admin(), $permission);
    }
}

if (!function_exists('lurl')) {
    /**
     * $params accepts the same shorthand as route() — pass a string for a single
     * required parameter (e.g. lurl('blog.show', $slug)) or an array for many.
     *
     * SECURITY: $locale comes from app()->getLocale() which is set by SetLocale
     * middleware against a hardcoded whitelist (en/th/zh/ru). NEVER replace
     * that lookup with a value derived from user input — the regex below
     * trusts $locale to be a-z lowercase only.
     */
    function lurl(string $name, mixed $params = [], bool $absolute = true): string
    {
        $url = route($name, $params, $absolute);
        $locale = app()->getLocale();
        if ($locale === 'en') return $url;

        // Inject the locale segment right after the host.
        return preg_replace('#^(https?://[^/]+)(/.*)$#', '$1/'.$locale.'$2', $url, 1);
    }
}
