# Security review and hardening — 2026-11-03

This is a source review and local verification, not a penetration test or a guarantee against attacks. No production environment, VPS firewall, web-server configuration or database was changed. Existing staged TURN work was preserved.

## Changes

- Removed the public `/api/psychologist-profiles/test` endpoint, which serialized full psychologist profiles and related users.
- Restored Laravel CSRF verification for consultation join/leave/end, ratings and session notes. The existing consultation fetch helper already sends CSRF headers, and Axios already uses the XSRF cookie. Regression checks reject missing tokens and accept valid tokens.
- Restricted the login `redirect` query parameter to application-relative paths, excluding external URLs, protocol-relative paths, backslashes and control characters.
- Added rate limits to registration, login, password reset and password confirmation, in addition to existing per-account login throttling.
- Role-protected routes now reject deactivated users even when they retain an existing session.
- Added `nosniff`, same-origin framing, a restrictive cross-origin referrer policy and `no-store` for authenticated web responses.
- Reporter identity now comes from the authenticated user instead of caller-supplied IDs/types.
- Removed complete profile request/data logging and prevented session-note/rating SQL exception messages from placing submitted clinical text in application logs.
- Replaced Vue `v-html` sinks with a shared DOMPurify-backed `v-safe-html` directive. Normal HTML formatting and pagination labels remain; executable markup, event handlers and unsafe URL schemes are removed. This does not claim to audit every third-party editor or dynamically constructed modal.
- Updated Vue, PostCSS, Axios, Swiper and WebSocket dependency locks within compatible major versions. Removed the obsolete `@inertiajs/inertia` client and used the existing Vue adapter's router. Added DOMPurify and jsdom (test-only).
- WebSocket room access now requires a short-lived signed token issued by Laravel only to the assigned participant of a confirmed, noncompleted consultation. The server derives room, role and display name from the signature rather than trusting caller claims. It rejects unsigned/expired joins, restricts session-ended signaling to psychologists, limits message size/rate, disables compression and times out unauthenticated sockets. Room creation, signaling messages and UI remain otherwise intact.

## Required coordinated deployment

**The signed signaling change requires deployment settings. Do not deploy just one side.**

1. Generate a new random secret of at least 32 characters (for example, `openssl rand -hex 32`) privately. Set the identical `SIGNALING_AUTH_SECRET` value in Laravel's `.env` and in the Node signaling process environment. Use a dedicated value, separate from `APP_KEY` and `TURN_SHARED_SECRET`. Never prefix it with `VITE_`, log it or commit it.
2. The standalone Node server reads `process.env`; it does not automatically load Laravel's `.env`. Supply the secret through your existing process manager or container runtime. The current Compose file does not pass it automatically; explicitly add it to that deployment's environment. Node refuses to start without it, and Laravel refuses to issue tokens without it.
3. Install the committed locks: `npm ci` at the application root and `npm ci --omit=dev` inside `signaling`. Generated tracked `signaling/node_modules` files were deliberately not updated; always install dependencies from the lockfile when deploying. Preserve the existing TURN secret and signaling URL.
4. Build the frontend with `npm run build`. Refresh Laravel's configuration cache with `php artisan config:cache`. If routes are cached, refresh them using your deployment procedure. Deploy/restart Laravel, Node and the matching frontend together. Existing frontend builds cannot join the hardened signaling server; refresh both participants' browsers.
5. Verify login/logout, registration, password reset, clinical note save/edit, ratings and consultations in staging. Test both join orders, chat/media signaling and completed-session rejection. A caller without a signed token must receive WebSocket close code 1008. Do not paste tokens or clinical data into tickets.

## SQL injection review

Reviewed raw SQL and query construction in `app`, routes and config. No clear untrusted interpolation was found in the inspected queries. Raw expressions use constants/bindings; the dashboard's date-format strings are chosen by a fixed allowlist. Eloquent/query-builder values use parameter binding. This is not proof that every database flow is free of SQL injection, and no live attack testing was performed.

References: https://laravel.com/docs/10.x/database and https://laravel.com/docs/10.x/queries

## Outstanding findings

- **Unsupported PHP framework and vulnerable PHP dependencies:** `composer.lock` contains Laravel 10.48.29. Laravel 10 security support ended February 4, 2025. `composer audit --locked --no-dev` reported advisories for Laravel, Guzzle/PSR7, CommonMark, Flysystem, PsySH and several Symfony components. A dry-run update within existing constraints was rejected by Composer's security advisory blocker; the Laravel 10 constraint and old Inertia Laravel adapter prevent resolving a safe supported stack. No advisories were ignored and no insecure override was enabled. A tested Laravel/Inertia upgrade is required; current code changes do not eliminate these library vulnerabilities.
- Root `npm audit --omit=dev` after targeted updates: **4 low, 0 moderate/high/critical** findings (Quill, Vue i18n and transitive dependencies). This classification follows package manifests; some dependencies under devDependencies are still compiled into the browser.
- Full root `npm audit`: **8 low, 17 moderate, 6 high, 0 critical** findings remain, including build-tool/transitive advisories. Update the Vite/build tooling and address the Vue i18n prerelease dependency with a tested migration. A broad update currently conflicts because newer i18n 12 prereleases require a Vue beta. Do not resolve it with `--force` or expose the Vite development server publicly.
- Standalone signaling `npm audit --omit=dev`: **0 findings** against its updated lockfile. This is a dependency result, not a guarantee about application logic.
- Sensitive verification uploads currently include public-storage/Cloudinary paths. Moving existing identity/bank/clinical documents behind authorized downloads requires a storage and data migration, including revoking old public URLs. That migration was not performed. Review retained application/payment logs and published document access; previously exposed credentials require rotation.
- Production posture is unverified: confirm `APP_ENV=production`, `APP_DEBUG=false`, secure session cookies over HTTPS, web root restricted to `public/`, denied access to `.env`/`.git`/backups, a minimally privileged database account, TLS/WSS, OS patches, backups and edge request/connection limits. The application middleware is not DDoS protection. Apply trusted-proxy settings only for your actual proxy addresses.

Framework support: https://laravel.com/docs/13.x/releases#support-policy
Production deployment: https://laravel.com/docs/10.x/deployment

## Verification performed

- `php tests/security-check.php`: passes redirects, CSRF rejection and acceptance, private response headers, active/blocked account checks, removed route and participant-only token issuance/signature/expiry. Uses only an isolated in-memory SQLite database for token tests.
- `node --test signaling/auth.test.js signaling/server.test.js tests/safe-html.test.js`: passes token validation and forgery/expiry rejection; real local WebSocket access/role/room isolation and offer/chat delivery; HTML sanitization and pagination preservation.
- Frontend production build succeeded to a temporary output directory; it was removed after validation. PHP syntax checks and route registration checks passed.
- PHPUnit is not installed in this checkout; the full application test suite was not run. No end-to-end browser/VPS security test, payment regression test or production deployment was performed.
