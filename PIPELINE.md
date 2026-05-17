# PKSB Pipeline

Single source of truth for everything still on the runway, plus a record of what's already landed. Pulled from 6 audits this session: SEO/UX, UX/UI, security, full-project, design critique, and Pream's two reviews.

**Format:** each item has Priority · Effort · Source · Status. Shippable in 2 weeks of focused work.

---

## ✅ Already shipped this session

### Architecture & i18n
- [x] URL-prefixed locales (`/`, `/th`, `/zh`, `/ru`) with hreflang + sitemap alternates
- [x] `lurl()` helper for locale-preserving link generation
- [x] Per-post translation system (`translations.{locale}` with EN fallback)
- [x] Bulk translation of all 5 destinations into TH/ZH/RU
- [x] Tabbed translation editor in admin (Quill per language)
- [x] Locale stays sticky to URL only — no session lock-in (UX bug fix)

### Admin & RBAC
- [x] Multi-user admin with `users.json` + bcrypt
- [x] Three roles: `owner` / `editor` / `translator`
- [x] AdminAuth + AdminCan middleware
- [x] Permission-aware sidebar nav, action buttons, form fields
- [x] Anti-lockout safety rails (can't delete self, can't demote last owner)
- [x] Left-sidebar redesign with role-coloured user card
- [x] Mobile sidebar drawer (slide-in + backdrop + Esc to close)

### Content management
- [x] PostsRepository (JSON store at `storage/app/posts.json`)
- [x] Multi-photo upload with cover/gallery management
- [x] EXIF stripping + re-encode through Intervention/Image
- [x] TimetableRepository with multi-image + per-locale captions + reorder
- [x] PassesRepository with per-locale name/desc + cover upload + reorder
- [x] Visual/HTML toggle with Quill 2 (CDN + SRI hashes)
- [x] Future-date scheduling (`published_at` > today hides from public)

### Public site
- [x] All pages + blog index + blog show + sitemap.xml
- [x] OG/Twitter Card tags + canonical + JSON-LD (LocalBusiness, Article, ItemList, BusTrip)
- [x] WebP companions for bundled hero/feature photos
- [x] `pass-30days.png` 925KB → `.webp` 84KB (10× reduction)
- [x] Self-hosted Inter via `@fontsource/inter` (no Google Fonts CDN)

### Security (B-series + H-series from audit)
- [x] **B1** HTMLPurifier sanitization on all translation bodies (XSS killed)
- [x] **B2** Boot guard refuses production with weak `ADMIN_PASSWORD`
- [x] **B3** Boot guard refuses production with `APP_DEBUG=true`
- [x] **B4** Rate limit on `/admin/login` (10/min per IP+username, 30/min per IP) + failed-login logging
- [x] **H1** `SecurityHeaders` middleware (CSP, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, conditional HSTS)
- [x] **H2** SRI hashes locked on Quill CDN tags
- [x] **H3** `starts_with:` validation on cover/photo paths (path traversal closed)
- [x] **H4** Iframe sandbox on `/tracking`
- [x] `LOCK_EX` on every JSON write across all repos
- [x] CSRF on every admin form (Laravel default + `@csrf`)
- [x] Session regeneration on login

### Infrastructure
- [x] Vite-built CSS (35KB → from 300KB Tailwind CDN dev build)
- [x] Backup script `scripts/backup.sh` with 14-day rotation
- [x] GitHub Actions CI (PHP test + Node build + composer audit)
- [x] 15 feature tests passing (public site, locale, sitemap, security headers, admin auth, RBAC matrix, HTML sanitizer)
- [x] Seed data moved from `config/posts.php` → `database/seed/posts.php`
- [x] Drag-drop photo upload with previews

---

## 🚨 Day-1 launch blockers (must do before going live)

| # | Item | Source | Effort | Notes |
|---|---|---|---|---|
| L1 | Generate strong `ADMIN_PASSWORD` for production `.env` | Security B2 | 5m | Boot guard exists; production must rotate |
| L2 | Set `APP_ENV=production`, `APP_DEBUG=false` | Security B3 | 5m | Boot guard refuses startup otherwise |
| L3 | Set `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN=phuketsmartbus.com` | Security M3 | 5m | Hardens session storage |
| L4 | `php artisan key:generate --force` on first deploy | Std | 1m | New APP_KEY per environment |
| L5 | `php artisan storage:link` on the production box | Std | 1m | Symlink for `/storage/...` |
| L6 | Verify `.env` is **not** in deploy artifact / web-reachable | Security | 5m | Smoke test: `curl /storage/.env` → 404 |
| L7 | After first login, delete the `admin` seeded account, create real owner | Security | 2m | Document in deploy runbook |
| L8 | Add backup cron line to crontab | Reliability | 5m | `15 3 * * * /var/www/pksb/scripts/backup.sh` |
| L9 | Run `composer install --no-dev --optimize-autoloader` + `npm ci && npm run build` + `php artisan config:cache route:cache view:cache` | Std | 5m | Production warmup |

**Total day-1 effort:** ~30 minutes if everything else lands first.

---

## 🔥 Sprint 1 — UX polish (this week, ~6 hours)

### Critical UX from design + ux/ui audits

- [ ] **D-S6** Collapse Day Pass admin to summary rows · Design · **2h** · 4 passes × 520px → 4 × 80px when collapsed. Click row to expand inline OR drawer. Highest-impact UX fix.
- [ ] **D-S7** Promote "+ Add a new pass" `<details>` summary to a real button · Design · **15m** · Match the "+ New Destination" visual weight on posts page.
- [ ] **U-U2** Tab the post edit form (Content / Photos / Settings) · UX/UI · **2h** · 1500px-tall form is overwhelming on mobile.
- [ ] **U-U1** Empty states everywhere · UX/UI · **45m** · `/admin/posts`, `/admin/timetables`, `/admin/passes` when empty. Arrow-to-CTA pattern.
- [ ] **D-S3** Standardize card component · Design · **1h** · Three variants (blog/pass/feature) → one card token. Pick: `bg-white rounded-2xl ring-1 ring-gray-100 shadow-sm hover:shadow-md`.
- [ ] **D-S5** Posts table row rhythm · Design · **5m** · `py-4` + `hover:bg-gray-50` (linter already did it on users — match here).
- [ ] **D-S8** Sidebar bottom version stamp · Design · **5m** · Eliminates 400px of empty navy.
- [ ] **U-U?** Confirm modal instead of native `confirm()` · UX/UI · **30m** · Tailwind dialog component.

---

## 🟠 Sprint 2 — Brand & polish (next week, ~4 hours)

### Brand consistency

- [ ] **D-S1** Logo lockup variants (`logo-light.png` for dark backgrounds, `logo-dark.png` for light) · Design · **30m** · Stop using `brightness-0 invert` CSS hack.
- [ ] **D-S2** Login screen visual upgrade — bus photo backdrop at 0.15 opacity, or coastline silhouette · Design · **30m** · First impression brand expression.
- [ ] **D-S4** "View Site" sidebar link — bigger external-link icon, slightly clearer demotion · Design · **5m**

### Spacing micro-fixes (batch them)

- [ ] Hero CTAs `gap-3` → `gap-3 sm:gap-4` · 1m
- [ ] Blog post hero breadcrumb spacing → `mb-6` · 1m
- [ ] Posts table "Photos" column → "3 photos" instead of bare number · 2m
- [ ] Day pass overlay `tracking-tight` on headlines · 1m
- [ ] Footer copyright `text-white/60` consistency · 1m

### Per-language a11y

- [ ] Add `lang="th"` etc. on per-language `<input>` and rendered `<article>` · A11y · **30m** · Screen readers + Chrome spellcheck behave correctly.
- [ ] `autocomplete` attrs on login form (`username`, `current-password`) · A11y · **2m**

### Performance

- [ ] **P2** Re-encode hero photos at WebP q=75 → another 30% smaller · **15m**
- [ ] **P5** Cache-Control headers on `/storage/destinations/*` (immutable, max-age=31536000) · **20m** · Hashed filenames are safe to cache forever.
- [ ] Generate WebP companions automatically on photo upload (in `ImageUploadService`) · **45m**

---

## 🟡 Sprint 3 — Reliability & power features (week 3, ~6 hours)

### Code quality

- [ ] **A1** Extract route closures to controllers · Architecture · **2h** · Routes file is 600+ lines. Per-resource controller (`PostsAdminController`, `TimetablesAdminController`, etc.).
- [ ] **A2** Move validation arrays to FormRequest classes · Architecture · **1h** · Easier to test, easier to share rules.

### Power-user features

- [ ] `Cmd+S` to save in admin forms · UX · **15m**
- [ ] `?` keyboard shortcut to show shortcut cheatsheet · UX · **30m**
- [ ] Image `loading="lazy"` on admin photo grids · Perf · **5m**
- [ ] Per-file upload progress (chunked Fetch endpoint) · UX · **2h** · Defer if backlog tight.

### Security M-series (medium)

- [ ] **M2** Bump password policy: `min:10`, mixed-case + numbers required · Security · **15m**
- [ ] **M3** Set `SESSION_ENCRYPT=true` (also in deploy checklist) · Security · **already in L3**
- [ ] **M4** Failed-login audit log to a dedicated channel/file · Security · **15m** · Already logging via default channel; split out `security.log`.
- [ ] **M5** Comment in `lurl()` warning never to take locale from user input · Security · **1m**

### Reliability

- [ ] **R1** `LOCK_SH` on JSON reads (not just writes) · Reliability · **30m** · Prevents partial-read on concurrent write.
- [ ] **R2** Verify backup script restore path (test recovery once) · Reliability · **30m**

---

## 🔵 Sprint 4 — Scale prep (when needed, not now)

Hold these until usage signals demand them.

- [ ] **Migrate JSON → SQLite** when posts > 100 OR concurrent admin writes get noticeable. Repo abstraction is already in place; swap is ~2 days. *Trigger: read time on `/blog` > 200ms.*
- [ ] **Multi-province / multi-tenant** — when PhuketSmartBus expands to other Thai provinces. Probably MySQL + tenant scoping. *Trigger: 2nd province signs.*
- [ ] **2FA on owner accounts** (`pragmarx/google2fa`) · Security · **3h** · Worth it once non-Pream operators have owner access.
- [ ] **CSP nonces** for `script-src` (drop `'unsafe-inline'`) · Security · **2h** · Real lift but real win for XSS hardening.
- [ ] **Image CDN** (Bunny/Cloudflare R2) for `/storage/destinations/*` · Perf · **2h** · When the gallery hits ~500 photos.
- [ ] **Real mail backend** for the contact form (currently redirects to LINE) · Product · **1h** · When LINE-redirect friction shows up in support tickets.

---

## 📊 Cross-audit summary by category

Score from latest audit · trend since session start.

| Category | Score | Trend | Notes |
|---|---|---|---|
| Architecture | 7/10 | + | Repo abstraction holds; routes file is the next refactor |
| SEO foundations | 9/10 | ↑↑ | Per-page meta, OG, hreflang, JSON-LD, locale-prefixed URLs, sitemap with alternates |
| UX/UI | 7/10 | ↑ | Sidebar redesign + drag-drop done; Day Pass collapse is the next big win |
| Design system | 7.6/10 | ↑ | Brand cohesive, three card variants is the gap |
| Accessibility | 7/10 | flat | Skip-to-content, ARIA toggles done; per-language `lang` attrs missing |
| Performance | 7/10 | ↑ | Vite, WebP, self-hosted Inter; lazy upload progress is next |
| Security | 8.5/10 | ↑↑↑ | All 4 blockers + 4 high-priority items shipped this session |
| Reliability | 7/10 | + | LOCK_EX + backup script done; LOCK_SH on reads pending |
| Test coverage | 6/10 | ↑↑ | From 0 to 15 feature tests covering core flows |
| i18n | 9/10 | ↑↑ | URL-prefixed, fallback chain, all UI + content in 4 langs |
| **Production-readiness** | **8/10** | **↑↑↑** | After L1–L9 → genuinely launchable |

---

## 🗺️ Recommended order of operations

```
Today          → Sprint 1 (UX polish)         ─ 6h focused work
+ 1 week       → Sprint 2 (Brand & polish)    ─ 4h
+ 2 weeks      → Sprint 3 (Reliability)       ─ 6h
+ Production   → Run L1–L9 deploy checklist   ─ 30m
                ↓
                Soft launch
                ↓
+ Usage signal → Sprint 4 (Scale prep)
```

Total before public launch: **~16 hours of focused work** across ~3 weeks.

---

## 📝 Notes for the team

- **Pream's anti-bloat principle:** if a sprint item takes >2× the listed effort, stop and re-scope. Ship the smallest version that solves the user pain.
- **Don't add a 4th card variant.** D-S3 standardization should be backwards before forwards.
- **Don't expand the colour palette.** 6 colours is the max — adding a role colour will erode the system fast.
- **JSON storage is not the bottleneck yet.** Don't migrate to SQLite as a "future-proofing" task. Wait for the trigger metric.
- **The boot guard in `AppServiceProvider` is your safety net.** It's intentionally aggressive — if production won't start, that's the guard catching a misconfigured deploy. Read the exception message; don't disable the guard.

_Last updated: 2026-05-07 · Generated from session audits._
