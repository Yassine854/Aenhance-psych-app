# Consultation request and chat fixes — 2026-11-04

The consultation fetch helper previously assembled CSRF headers, then spread request options afterward. For JSON POST requests, `options.headers` replaced the complete header object and discarded the CSRF headers. Retrying repeated the same mistake. When both tokens were present, a stale page meta token could also take precedence over a refreshed cookie token.

`resources/js/utils/consultationRequest.js` now merges headers last, uses the current XSRF cookie before the page meta token, refreshes the cookie and retries a failed write once, and reports an expired session clearly. CSRF protection remains enabled. Both consultation roles use this helper through the shared panel.

The chat overlay now has an explicit responsive width. Its header/input cannot shrink, the message list has a constrained scrollable height, and long messages wrap inside the panel. The video stage has a 320px minimum height to prevent a short viewport from collapsing the call and chat controls. The appearance still needs checking on the deployed phone and PC.

Signaling configuration failures return an explicit JSON 503 response. WebSocket closures now distinguish authorization rejection (1008) from connectivity failures, while intentional hangup does not show a failure. No credentials are logged.

## Deploy and retest

Deploy the source changes, run `npm ci` and `npm run build`, and refresh Laravel's cached configuration using your normal deployment procedure. End existing calls and hard-refresh both devices (sign in again if necessary). Keep `VITE_FORCE_RELAY=0` for normal automatic ICE selection.

Check that the consultation's `/session/join` request succeeds and that `/signaling-token` returns 200 on both devices. Do not share either response body, cookies or authorization tokens. Start a fresh consultation between the PC and 4G phone and test chat at both narrow and short viewport sizes.

If 503 persists, inspect its exact request URL in the Network tab:

- `/appointments/{id}/signaling-token`: Laravel's running process/configuration must contain the same private `SIGNALING_AUTH_SECRET` as Node. Presence in a file alone does not establish runtime configuration. `php artisan config:cache` refreshes Laravel's cached settings. A safe local diagnostic is `php artisan tinker --execute="dump(strlen((string) config('signaling.secret')) >= 32);"`, which prints only a boolean. Do not dump the secret.
- A `wss://signaling.staging.aenhance.net` handshake: inspect the Node process and reverse-proxy logs/status. A browser may receive 503 when the proxy cannot reach its upstream process. A close code 1008 after a successful upgrade indicates rejected authorization; compare the configured secret, app/Node deployment versions and server clocks privately.
- A normal page request: inspect Laravel/proxy/maintenance status separately; a page-level 503 is not an ICE or TURN error.

During this local investigation, the public signaling health check returned 200 and a WebSocket handshake returned 101. No real room was joined remotely, and this does not verify the consultation, room token signature, TURN or media flow.

## Local checks

- `node --test tests/consultation-request.test.js tests/safe-html.test.js signaling/auth.test.js signaling/server.test.js`
- `php tests/security-check.php` (isolated in-memory SQLite for token checks)
- Frontend production build and PHP syntax check.

The request tests cover merged JSON/CSRF headers, stale-meta precedence, one retry with a refreshed cookie, persistent 419 handling, and preserved 503 diagnostics.

## TURN credential 503 and signaling close 1006

The later screenshot shows `/turn-credentials` returning 503. Chat uses WebSocket signaling and can work without any media relay. This error prevents the browser from obtaining TURN credentials; investigate credential delivery before testing Coturn ports or relay media.

The application now returns `code: turn_not_configured` if its runtime `config('turn.secret')` is empty. If that code is present, set `TURN_SHARED_SECRET` (including the final T) in the environment of the Laravel deployment actually serving the request. It must match the existing Coturn `static-auth-secret`; it is different from `SIGNALING_AUTH_SECRET`. From that deployment's Laravel directory run:

```bash
php artisan config:clear
php artisan config:cache
php artisan tinker --execute="dump((string) config('turn.secret') !== '');"
```

The last command prints only a boolean. If it is true but the browser still receives this application error, check that PHP-FPM/long-running workers and all application instances run the same deployment/configuration; restart them through the existing deployment process if required. A generic HTML 503 instead of this JSON code points to a server/proxy failure and needs its logs/status checked. Do not share the credential endpoint's successful response.

Deploy the updated frontend and rebuild it. With `VITE_FORCE_RELAY=0`, STUN/direct connections remain available when TURN credentials are unavailable, and the panel reports the missing relay fallback. In a relay-only debugging build, missing credentials stop negotiation instead of falling back silently. The TURN badge is now set only by selected-candidate statistics, not by the debug flag.

Deploy the updated Node signaling sources, including `heartbeat.js`, and restart/rebuild the existing signaling process/container. Native ping/pong runs every 25 seconds independently of chat traffic to keep idle proxy connections active and clean up unresponsive sockets. Browser code 1006 means an abnormal closure; it does not identify its cause. Heartbeats mitigate idle timeouts but cannot fix a crashed upstream or proxy misconfiguration. If it recurs, correlate the timestamp with Node and reverse-proxy logs, and verify proxy WebSocket upgrade/read-timeout settings.

Local runtime checks loaded a nonempty TURN secret, and isolated controller checks pass for configured/missing credentials and HMAC signing. Neither proves that the failing deployed installation is configured or that relay media works. Require HTTP 200 from the credential endpoint on both devices, then inspect the selected relay candidate pair and increasing media counters in a fresh call.
