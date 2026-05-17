<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pream's note for the team:
 * One place to manage every security header. CSP is intentionally pragmatic:
 *   - 'self' for scripts + styles
 *   - jsdelivr.net allowed for Quill (admin form)
 *   - Google Fonts allowed because Tailwind CSS imports them
 *   - inline styles allowed because Tailwind 4 + svg fill="..."
 *   - frame-src includes smartbus.phuket.cloud for the live map page
 * Tighten via nonces in a follow-up — for now, this catches the easy XSS
 * (cookie exfiltration to attacker domains) without breaking the build.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(self), interest-cohort=()'
        );

        // CSP — same policy for public + admin. Adjust if you ever ship a
        // tighter admin-only policy.
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "font-src 'self' data:",
            "img-src 'self' data: blob: https://*.tile.openstreetmap.org https://www.google-analytics.com https://www.googletagmanager.com",
            "connect-src 'self' https://www.google-analytics.com https://analytics.google.com https://stats.g.doubleclick.net",
            "frame-src https://smartbus.phuket.cloud",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self' https://line.me",
            "object-src 'none'",
        ];
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // HSTS only in production over HTTPS.
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // P5: long-term cache on user-uploaded images. Filenames are random
        // hashes (see ImageUploadService), so they're safe to mark immutable.
        $path = $request->getPathInfo();
        if (str_starts_with($path, '/storage/destinations/')
            || str_starts_with($path, '/storage/timetables/')
            || str_starts_with($path, '/storage/passes/')
        ) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        return $response;
    }
}
