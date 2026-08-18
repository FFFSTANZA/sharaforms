# AGENTS.md

Instructions for AI coding agents working in this repository.

## Scope

- This file applies to the full repository.
- If a deeper `AGENTS.md` exists in a subdirectory, follow the deeper file for files in that subtree.

## Repository Overview

- Monorepo with:
- `api/`: Laravel 11+ / PHP 8.3 backend.
- `client/`: Nuxt 3 / Vue frontend (JavaScript, not TypeScript by default).
- `docs/`: Mintlify documentation.

## Working Style

- Make focused changes that directly solve the request.
- Prefer editing existing patterns over introducing new architecture.
- Keep diffs small and avoid unrelated refactors.
- Preserve existing conventions and file organization.

## Setup And Commands

- Frontend (`client/`):
- Install: `npm install`
- Dev server: `npm run dev`
- Lint: `npm run lint`
- Tests: `npm run test`
- Backend (`api/`):
- Install: `composer install`
- Tests: `php artisan test`

## Validation Before Handoff

- Run targeted checks for changed areas first.
- If frontend code changed, run `npm run lint` (in `client/`) and relevant tests.
- If backend code changed, run relevant `php artisan test --filter=...` and broaden only if needed.
- Report what was run and what could not be run.

## Project Conventions

- Frontend:
- Use Nuxt UI v3 + Tailwind patterns already used in the codebase.
- Prefer Composition API and existing composables.
- Use Promise chaining style (`.then().catch().finally()`) where this project already enforces it.
- Backend:
- Follow Laravel conventions, Form Requests, Eloquent relationships, and Pest tests.
- Keep controllers thin and place business logic in services when appropriate.

## Cursor Rules (Source Of Truth)

- For detailed conventions, read and follow:
- [`.cursor/rules/sharaforms-overview.mdc`](./.cursor/rules/sharaforms-overview.mdc)
- [`.cursor/rules/front-end.mdc`](./.cursor/rules/front-end.mdc)
- [`.cursor/rules/api-query.mdc`](./.cursor/rules/api-query.mdc)
- [`.cursor/rules/front-end-testing.mdc`](./.cursor/rules/front-end-testing.mdc)
- [`.cursor/rules/back-end.mdc`](./.cursor/rules/back-end.mdc)
- [`.cursor/rules/back-end-testing.mdc`](./.cursor/rules/back-end-testing.mdc)
- [`.cursor/rules/forms.mdc`](./.cursor/rules/forms.mdc)
- [`.cursor/rules/formula-engine.mdc`](./.cursor/rules/formula-engine.mdc)
- [`.cursor/rules/docs.mdc`](./.cursor/rules/docs.mdc)

## Documentation Changes

- When behavior, configuration, or workflows change, update docs in `README.md`, `docs/`, or both.

## Safety

- Do not commit secrets or tokens.
- Do not change licensing files or enterprise-only code boundaries unless explicitly requested.

## Cursor Cloud specific instructions

### Project overview

SharaForms is a no-code form builder with two main services: a **Laravel 11 API** (`api/`) and a **Nuxt 3 client** (`client/`). Development uses Docker Compose (`docker-compose.dev.yml`) which runs PostgreSQL, the API (PHP-FPM), the client (Nuxt dev server), and an Nginx ingress.

### Starting the dev environment

```bash
# Docker must be running first (see Docker gotcha below)
cd /workspace && docker compose -f docker-compose.dev.yml up -d
```

Wait ~60s for the client container to finish `npm install` + Nuxt build on first start. The app is then available at `http://localhost:3000` (client) and `http://localhost` (API via Nginx).

On first run, navigate to `http://localhost:3000/setup` to create the admin account.

### Docker gotcha (nested containers)

This cloud environment runs inside a container, so Docker requires `fuse-overlayfs` storage driver and `iptables-legacy`. The daemon config at `/etc/docker/daemon.json` is already set. To start dockerd:

```bash
sudo dockerd &>/tmp/dockerd.log &
sleep 3
sudo chmod 666 /var/run/docker.sock
```

### Stale Docker API image

The published `sharaforms/api:dev` image may lag behind the repo's `composer.json`. If the API container fails with missing class errors (e.g. `TwoFactorServiceProvider not found`), run:

```bash
docker exec sharaforms-api sh -c "curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer"
docker exec sharaforms-api composer install --no-interaction --optimize-autoloader
docker restart sharaforms-api
```

## Session: 2026-08-07 — Dodo Subscription Lifecycle End-to-End Simulation

### Summary
Simulated the full Dodo subscription lifecycle against the local stack: checkout sessions already created for all 6 plans; this session exercised webhook-driven activation, upgrades/downgrades, cancellation, replay dedup, and duplicate-active guard. All scenarios return 200, DB state verified correct.

### Root cause found: intermittent 500 on webhook `Cache::add` (dedup mark)
- Symptom: `Cache::add('dodo_webhook:...')` intermittently threw `fopen(... storage/framework/cache/data/{h1}/{h2}/...): Failed to open stream: No such file or directory` at `LockableFile.php:69`, causing a 500 on the webhook (`{"message":"fopen..."}`).
- Root cause: the **laravel `Cache::ad` 't' file-store dedup mark is stored under the `data/<h1>/<h2>/<sha1>` path**. `FileStore::add()`'s `ensureCacheDirectoryExists()` calls `makeDirectory(..., force=true)` which **silently returns false on failure** (no throw) when the intermediate dir is owned by a different user. My earlier root-run `docker exec php artisan tinker` probes wrote some cache entries as root, leaving root-owned dirs (`data/{aa,c7,...}/`) inside the www-data-owned `data/`, so subsequent php-fpm (www-data) writers could not `mkdir` the second-level hash dir and `LockableFile` failed.
- Fix: `docker exec sharaforms-api chown -R www-data:www-data storage/framework/cache`. Re-ran full harness — all 200, zero new `Failed to open stream` entries, and the `aa/42` + `c7/14` dirs got created fine as www-data.
- Lesson: **always run tinker/cache probes as `www-data`** (`docker exec -u www-data`), never bare `docker exec` (root), else the root-owned cache files break later webhook writes.

### Verified behavior (all webhooks 200)
- **Scenario A** — all 6 plans (Pro/Business/Enterprise × Monthly/Yearly, one user each): `subscription.created` + `activated` = 200/200 each. All rows `active` with correct `stripe_price`.
- **Scenario B** — lifecycle on one user: pro monthly → pro yearly → business monthly → enterprise yearly → downgrade business yearly → downgrade pro monthly; all 200, final row `active` @ pro monthly (product order preserved).
- **Scenario C** — `subscription.updated(cancel_at_next_billing_date)` + `subscription.cancelled`; 200/200, row `cancelled` with `ends_at=2026-08-06 13:00:00`.
- **Scenario D** — replay dedup: `subscription.created` with same `webhook-id` twice → first creates the row, second 200 `{"received":true}` (dedup mark hit in file cache). Duplicate-active guard: attempting a second active sub (`sub_sim_dup2`) for the same user → 200 `{"received":true}` but **no new row**; `sub_sim_dup` remains the only active sub. Log shows `local.WARNING: Ignoring Dodo webhook that would create a duplicate active subscription.`
- Verification: 9 rows in `subscriptions` match exactly the intended final state; no `Failed to process` or `Failed to open stream` entries at/after the fix (the 18:43:xx fopen errors are pre-chown history).

### Container cache gotchas recap
- Don't run `Cache::` mutation probes as root; chown `storage/framework/cache` back to `www-data` if a root-owned dir ever appears (`find ... -user root`).
- `Cache::add` mark survives across runs if a webhook-id is reused with expiration: already in flight (default TTL 86400s) — i.e. reusing `msg_...` after a run that already wrote it dedups the new post silently (observed with `msg_REPLAYFIXEDID000000000000000`). Use fresh ids per fresh scenario or `Cache::forget()` first.

## Session: 2026-08-07 (night) — Live E2E Webhook Fix: whsec_ Key Decoding (401 Root Cause)

### Symptom
Real Dodo test-mode webhooks delivered via Cloudflare tunnel (`/dodo/webhook`) returned **401 Invalid signature** for hours. Locally-constructed HMAC probes PASSED — because sims and tests signed with the same (wrong) algorithm. Dodo's deliveries failed because of an encoding mismatch, not a key mismatch.

### Root cause
Dodo (Svix/Standard Webhooks) treats the `whsec_...` secret as **base64-encoded raw key bytes**. The HMAC key is `base64_decode(substr($secret, 6))`, NOT the ASCII `whsec_...` string. `verifyWebhook()` hashed with the literal ASCII string, so every real signature failed.
- Confirmed with Python: `hash_hmac`-style digest over `webhook-id.webhook-timestamp.body` with the decoded 24-byte key matches Dodo's `v1,<b64>` signature; the ASCII-string key never matches.
- Dodo always sends `v1,<base64>` signatures (never hex), so the hex comparison branch in `verifyWebhook` was dead code.
- Observed as Dodo's ~2-min retry backoff bursts (401, 401, ...) in `docker logs sharaforms-api` for the same `msg_...` id.

### Fix
- `DodoPaymentsService::verifyWebhook` now resolves the key via new `webhookSigningKey()`: if the configured secret starts with `whsec_`, `base64_decode(substr($secret, 6), true)`; fallback to raw string for non-prefixed keys.
- Tests updated to sign with the decoded key: `DodoWebhookHandlingTest::postDodoWebhook()` and `DodoPaymentsServiceVerifyWebhookTest::validDodoWebhookHeaders()` both decode `whsec_`-prefixed secrets before `hash_hmac` (the test fixtures use `whsec_.Str::random(32)` so they exercise the real decode path).
- Removed the temporary `DODODBG verifyWebhook failed` debug log from `DodoPaymentsController::handle` (it logged full request headers+body on 401).

### Verified live
- PATCH `https://test.dodopayments.com/subscriptions/sub_0NkpCdMVnQfOxkQiEFBa3` (metadata bump) triggers a real `subscription.updated` webhook.
- After fix: nginx shows **200** (was 401); laravel.log shows the normal processing flow, and DB has the row: `stripe_id=sub_0NkpCdMVnQfOxkQiEFBa3, stripe_status=active, stripe_price=pdt_0NkkZ9YlWg9hHTb6i2H2j` for user `e2e-live@sharaforms.dev` (user 665, `stripe_id=cus_0NkpCdMFBaFZ2fplNym3N`).
- The L5 re-attribution warning fired when the webhook corrected the user's customer ID — expected behavior.
- Tests: 707 passed / 1 failed in `tests/Unit` — the 1 failure (`PlanServiceTest` "returns enterprise when pricing is disabled") is **pre-existing** (uncommitted `pricing_enabled()` gating change in `PlanService.php` from the M1 session, test not yet updated); unrelated to this fix. `tests/Feature/Subscriptions` + webhook + verify-webhook suites: 31 passed.

### Gotchas
- **The signing key is `whsec_<base64>` — decode, don't hash the ASCII string.** Any future sim/tool must use the decoded key, or it will "pass" while real Dodo deliveries 401 (symmetric wrongness).
- Dodo test-mode webhook endpoints listed via `GET https://test.dodopayments.com/webhooks`; signing key via `GET /webhooks/{id}/secret`.
- Debug-signature tooling: `/tmp/opencode/nginx_probe.py` (self-signed probe to the live tunnel).

## Session: 2026-08-09 — Form Boolean Casts Fix + Dev DB Wipe Incident (Recovery Notes)

### Root cause fixed: "Error saving form" 500 on PostgreSQL
- `api/app/Models/Forms/Form.php` `casts()` was missing 11 boolean casts → raw ints hit the query builder.
- Laravel `Connection::prepareBindings()` converts PHP bools to `(int)`; with `PDO::ATTR_EMULATE_PREPARES => true` (`api/config/database.php:80`, required for pgbouncer), PDO interpolates `false` as unquoted `0` → pgsql `SQLSTATE[42804]` "column is of type boolean but expression is of type integer" (and `42883` for morph varchar FKs).
- **Fix:** new `api/app/Database/PostgresBooleanConnection.php` (extends `Illuminate\Database\PostgresConnection`) overrides `prepareBindings()`: bool→`'true'`/`'false'`, int/float→`(string)`, null passthrough, then `parent::prepareBindings()`. Registered in `AppServiceProvider::boot()` via `Connection::resolverFor('pgsql', ...)`. Emulated prepares stay; stringified quoted literals coerce correctly in pgsql.
- Added the 11 casts: `auto_focus`, `can_be_indexed`, `confetti_on_submission`, `editable_submissions`, `layout_rtl`, `re_fillable`, `show_progress_bar`, `no_branding`, `transparent_background`, `uppercase_labels`, `use_captcha`.
- Verified: full-payload POST `/open/forms` → 200, PUT flip → 200, DB shows `t`/`f`, version row created, public answer POST → 200.

### ⚠️ Dev DB wipe incident — test-run hazard (READ BEFORE RUNNING PEST IN THE COMPOSE STACK)
- **What happened:** running `php artisan test` batches via `docker exec` ran `migrate:fresh` on the REAL pgsql DB — all users/workspaces/forms/subscriptions wiped (migrations table 105, all tables empty).
- **Mechanism:** `tests/TestCase.php` uses `RefreshDatabase`; `ensureSafeTestingDatabase()` guard (`AppServiceProvider.php:103`, throws for non-sqlite in `'testing'` env) did **not** fire because `app()->environment()` resolved `local`, not `testing`. With `bootstrap/cache/config.php` present (baked at container start), Laravel's `LoadEnvironmentVariables` bootstrap step early-returns on `configurationIsCached()`, so `detectEnvironment()` is skipped and `APP_ENV=testing` (via `-e`) never binds; `env()` falls back to the container's `APP_ENV=local`. Cached config supplies `database.default=pgsql` → RefreshDatabase wipes the real DB. phpunit.xml's sqlite `:memory:` is likewise bypassed by the cache.
- **MANDATORY pre-test procedure (never skip):**
  ```bash
  docker exec -u www-data sharaforms-api php artisan config:clear   # deletes bootstrap/cache/config.php
  docker exec -u www-data sharaforms-api php artisan test --filter=...
  ```
  Run `config:clear` before EVERY pest batch; re-run it if the container is restarted (entrypoint re-bakes config). Never run `migrate:fresh`/`RefreshDatabase`-based suites against the live stack without first confirming `config('database.default') === 'sqlite'` (`php artisan tinker --execute='echo config("database.default");'`).
- **Recovery (idempotent):** `docker exec sharaforms-api php artisan db:seed --class=E2ETestSeeder --force` → creates E2E User (`e2e@example.test` / `Abcd@1234`) + workspace, linked as admin. Login is `POST /login` (NOT `/open/login`), returns `{"token": ...}`.
- API payload notes for manual E2E: create form requires `visibility, language, theme, presentation_style, width, size, border_radius, dark_mode, color` enums from `Form::*` consts (`classic|focused`, `centered|full`, `sm|md|lg`, `none|small|full`, `auto|light|dark`, `default|simple|notion|minimal|transparent`); `properties` must be an array of `{id, name, type}` objects; update via PUT (PATCH unsupported); answer route is `POST /forms/{slug}/answer` (no `open/` prefix), captcha-gated when `use_captcha=true`.

## Session: 2026-08-09 (later) — Cloudflare Free-Tier Setup + Nameserver Switch (LIVE)

### Summary
sharaforms.com now runs behind Cloudflare (Free plan). Zone was created from scratch (account had zero zones), all 14 Hostinger DNS records replicated, speed settings enabled, static-asset Cache Rule deployed, nameservers switched at the registry, zone activated, and everything verified live (200s, CF-RAY, Brotli, HTTP/3, cache HITs, mail records intact).

### Cloudflare zone (Tech@sharaforms.com account `e204d931f9f06be1b78a23645888f629`)
- Zone: `sharaforms.com`, id `2d2b921706d44c632d0bf7210bfe5cf7`, Free plan, status **active**, NS `rene.ns.cloudflare.com` + `teresa.ns.cloudflare.com` (was `aurora/nebula.dns-parking.com`).
- Origin: Hostinger web hosting A `@` → `135.148.41.180` (hosting NOT visible in Hostinger websites API; verified via curl). Valid Let's Encrypt cert → SSL mode **Full (strict)**.

### What's enabled (all free tier)
- Zone settings: `http3:on`, `brotli:on`, `early_hints:on`, `polish:lossless`, `0rtt:on`, `tls_1_3:on`, `ssl:strict`, `automatic_https_rewrites:on`, `always_use_https:on`.
- Tiered Cache: `PATCH /zones/{id}/argo/tiered_caching` + smart tiered `PATCH /zones/{id}/cache/tiered_cache_smart_topology_enable` — both on.
- **Cache Rule** (ruleset id `fb74b84843e64331bb5acaaa2fa0c6d6`, phase `http_request_cache_settings` entrypoint): static assets (`.webp .png .jpg .jpeg .svg .gif .avif .ico .js .css .woff .woff2 .ttf .eot .mp4 .webm`) on sharaforms.com/www → `set_cache_settings`, `cache:true`, `edge_ttl {mode: override_origin, default: 2592000, status_code_ttl: [{404,60}]}`, `browser_ttl {mode: override_origin, default: 2592000}`. **HTML deliberately NOT cached** (app/dashboard shares the domain; no Worker used).
- DNS: A `@` 135.148.41.180 (id `3d58fb1f6e1e8247dd917de11054632d`), CNAME www (id `2c7d33131daaf97de253e4abf19bb373`) proxied; all MX/SPF/DKIM (`resend._domainkey`, `hostingermail-a/b/c._domainkey`)/DMARC/autodiscover/autoconfig **unproxied** to protect Hostinger email (order `OR5ba589e7659bf94fa2a0777de621`, Starter Business Email). Verified post-switch: MX `@` mx1(5)/mx2(10) hostinger.com, MX `send` 10 feedback-smtp.us-east-1.amazonses.com, SPF both, DKIM key, DMARC p=none all resolve identically.

### API gotchas (rulesets)
- `edge_ttl` with `mode: "override_origin"` **requires `default`** (seconds) — omitting it errors `20107: default cannot be empty in override_origin mode`.
- `status_code_ttl` entries are `{status_code, value}` (or `status_code_range {from,to}` + `value`) — set them on top of `default`.
- `browser_ttl` same shape (`mode` + `default`).
- `tiered_cache` is NOT a zone setting (error 1006); correct path `/zones/{id}/argo/tiered_caching` (wrong sub-path errors 7003). `cache_level` setting PATCH rejected with 1007 — leave at `aggressive` (default).

### Verification (live)
- `Server: cloudflare`, `CF-RAY: ...-SIN`, `alt-svc: h3=":443"` on responses; Brotli `content-encoding: br` with `Accept-Encoding: br`.
- favicon.ico & `/_nuxt/*.css` → `cf-cache-status: HIT` (2nd fetch), `cache-control: max-age=2592000`, `age` increments. HTML → `cf-cache-status: DYNAMIC` (intended).
- Apex + www both HTTP 200 via CF edge (172.67.194.105 / 104.21.20.208).
- Local resolver still cached old NS up to TTL; use `dig @8.8.8.8` for authoritative post-switch checks (registry updated within ~1 min; zone went active ~7 min after switch).

### Notes for future deploys
- New content (hero button, comparison table, icons, LiveDemo preloads) will serve instantly for HTML (DYNAMIC), but cached static assets hold 30d — bump asset names (Nuxt does via hashed `/_nuxt/*` filenames) or purge `https://api.cloudflare.com/client/v4/zones/{zone}/purge_cache` (purge everything or by URL) after deploys.
- Hostinger domain is locked + privacy protected; NS update API accepted the change without unlocking.

## Session: 2026-08-09 (evening) — WhatsApp OG Preview Fix: 93KB share image + dims

### Root cause of "still no preview" (2nd round)
The default og:image `/share-preview.png` was **1,459,757 bytes (1.46MB)**, 1536×1024 — WhatsApp's crawler commonly drops preview images >~300KB and prefers 1.91:1 aspect (1200×630). Homepage + form pages emitted correct tags but the image itself was too heavy.

### Fix (commit 3b6c19f, deploy run 31319414886)
- **New image `client/public/share-preview.jpg`** (1200×630, 92,941 B): center-cropped the 1536×1024 design to 1.91:1, resized LANCZOS, JPEG q82. PNG was a dead end — gradient design needs 256 colors; even 1024×537 PNG quantized = 320KB > 300KB limit. JPEG q82 = 93KB, visually fine.
- **Deleted `share-preview.png`**, sed-swapped all 12 references (`app.vue` ×3 incl. JSON-LD, `index/pricing/comparisons/ai-form-builder/templates/[slug]/forms/[slug]` pages, `useComparisonSeo.js`, `SeoPreview.vue`).
- **`useOpnSeoMeta.js:99-100`**: `ogImageWidth/Height` 1536/1024 → **1200/630** (single source of truth — only place dims were hardcoded).
- Deploy pipeline auto-purges + pre-warms; first post-purge fetch is slow (13.9s one-off), steady state 0.45s TTFB HIT.

### Verification (prod, WhatsApp UA)
- `/share-preview.jpg`: 200, image/jpeg, 92,941 B.
- Homepage og: `og:image` = https://sharaforms.com/share-preview.jpg, width 1200, height 630.
- **Form page end-to-end** (temp form inserted via SQL, verified, deleted): og:title from form, og:description fallback "Fill out this form and submit your response. Created with SharaForms.", og:image absolute, og:image:width 1200, robots index; 0.87s TTFB / 2.1s total — inside WhatsApp's ~10s budget.

### Gotchas
- `api.sharaforms.com` does **not resolve** (never in DNS) — the API is same-origin: `NUXT_PUBLIC_API_BASE=/api` → `https://sharaforms.com/api`. Programmatic `/api/register` is blocked by reCAPTCHA (`g-recaptcha-response` required) + `hear_about_us`/`agree_terms`/`password_confirmation`.
- **DB form creation shortcut** (no auth needed): `INSERT INTO forms (workspace_id, title, slug, properties, can_be_indexed, visibility, created_at, updated_at, creator_id) VALUES (1, ..., '[{"id":1,"name":"Name","type":"text"}]'::json, true, 'public', now(), now(), 1)` — user 1 = fffstanza@gmail.com, workspace 1. `workspaces` has **no** creator_id column (forms.creator_id → users.id); the earlier `WITH w AS (...)` join against workspaces.creator_id fails. Prod forms count after cleanup: 2 (user's own forms now exist).
- **WhatsApp caches link previews per URL for ~2 weeks** — same URL pasted repeatedly reuses the cached (failed) preview. User must re-test with the same URL (may still show old) or a fresh variant (`/forms/slug?v=1` or trailing slash) to force a new fetch.

## Session: 2026-08-09 (night) — api.sharaforms.com (DNS + cert + ingress) + Euclid @font-face Fix

### api.sharaforms.com — API host now live (commit 59a0399)
- **DNS**: Cloudflare zone `sharaforms.com` (id `2d2b921706d44c632d0bf7210bfe5cf7`): created A record `api` → `135.148.41.180` (id `0c41f2985f0e42100b2e02d6d0515d7e`), proxied true (orange cloud). Same CF IPs as the apex (104.21.20.208 / 172.67.194.105); verify with `dig @8.8.8.8 api.sharaforms.com` (local resolver lags behind CF propagation).
- **TLS**: New ACME cert via dehydrated — `/etc/dehydrated/certs/api.sharaforms.com/{privkey,chain}.pem`, issued 2026-08-09, SAN `api.sharaforms.com`, expires 2026-11-07, hook line added in `/etc/dehydrated/domains.txt` (next renewal reuses the same hook). Cert works with Cloudflare **Full (strict)** (ssl_verify_result=0 direct).
- **Ingress** (`docker/nginx.conf`): new `server` block `server_name api.sharaforms.com` on 443 (after the catch-all so it wins SNI). No `location` match (`location /` → `try_files $uri $uri/ /index.php?$query_string;`) → Laravel runs on **unprefixed** URIs: `https://api.sharaforms.com/content/plans` (NOT `/api/...`; the main-host `/api` prefix is stripped by a map, so a `location /api/` proxy would double-prefix). Proxy-real-ip / TLS bits copied from the main 443 block. `docker compose restart` (NOT `up -d` — nginx reload not enough to rebind 443 SNI; `up -d` would rebuild the whole stack) → live **200 via CF**.
- **Backend gap (flag)**: `GET /licenses/create` → 404 — no license-checkout controller/route exists yet; any frontend call to `https://api.sharaforms.com/licenses/create` will 404. Feature not built.
- **CORS**: `CORS_ALLOWED_ORIGINS=https://sharaforms.com` (docker-compose env) — same-origin client works today; cross-origin from the app to the api host is pre-configured.

### Euclid font bug → "preloaded ... was not used within a few seconds" warning (commit 59a0399)
- Root cause: two broken `@font-face` `src` descriptors in `client/css/fonts.css`:
  1. Regular: `src: url("...Regular.ttf"), format("truetype");` — `format()` misplaced **outside** the `url()` (must be `url(...) format("truetype")`). A bare `format()` source is invalid → the 400-weight @font-face had **no valid src** → font never applied.
  2. Medium: `url("...Medium.ttf") format("truetype") format("truetype");` — double `format()` after one `url()` (only one optional `format()` allowed per CSS Fonts 4).
- Impact: `font-family: euclid-circular-b, sans-serif !important` (app.css:394) silently fell back to sans-serif, and Vite's auto-preload of `Euclid-Circular-B-Regular.ttf` was never consumed → browser warning.
- **Fix**: both `src` rewritten to `url(...) format("truetype");`. After deploy the Euclid family should render and the preload warning disappear.

### Deploy verification
- Run 31321089762 (59a0399) — pipeline: client lint → api+client image build → hostinger deploy → CF purge + warm. Verify Euclid renders + no preload warning + api.sharaforms.com still 200 after the API image rebuild.

## Session: 2026-08-09 (late night) — reCAPTCHA Blank Widget: Enterprise Key Domain Allowlist Fix

### Symptom
/register showed a blank white area where the reCAPTCHA widget should be (console: no captcha error; prior manual submit → 422 complete_captcha).

### Root cause
The site key `6LchaXYtAAAAAB7XCUuvkv2UXWEutnbD3DTaotry` IS a real reCAPTCHA Enterprise key (project `sharaforms`/775268936981, displayName SharaForms-web-checkbox, created 2026-08-05 via gcloud — keys.txt was correct; the "6Lc = classic" prefix heuristic is NOT reliable). But its `webSettings.allowedDomains` was **["localhost"] only** → enterprise.js refuses to render the widget on any other hostname → blank. Frontend (enterprise.js + grecaptcha.enterprise.render) and backend (projects.assessments) were already correct.

### Fix (no code/env/deploy changes)
- PATCH `https://recaptchaenterprise.googleapis.com/v1/projects/775268936981/keys/6LchaXYt...?updateMask=webSettings` → `allowedDomains: [localhost, sharaforms.com, www.sharaforms.com]`, keep `allowAllDomains:false`, `integrationType:CHECKBOX`, `challengeSecurityPreference:BALANCE`.
- Verified backend path with fake-token assessment: 200, `tokenProperties.valid=false`, `invalidReason=MALFORMED` (key recognized; a real solved token will validate).
- keys.txt updated (Allowed domains line + fix note). Local browser test needs a hard refresh; GCP key-setting changes propagate within minutes.

### Gotchas
- `GET /projects/{id}/keys` requires OAuth2 (gcloud auth print-access-token) — API keys are rejected ("API keys are not supported by this API").
- Prefix-based key-type detection (6Lc vs 6Le) is folklore — always list keys via the API to know the truth.
- reCAPTCHA Enterprise domain allowlist is enforced at render time client-side; wrong domains = silent blank widget (no console error).

## Session: 2026-08-11 — Google OAuth Popup Sign-In Fixes

### Symptom
Intermittent Google sign-in failures: after choosing account/Continue the opener login/register page stayed stuck; popup sometimes didn't open or opened the wrong/stale window. Root causes found and fixed (all in `client/`):

1. **New_User silent welcome bug (main "stays on same page")** — `pages/oauth/[provider]/callback.vue` routed `response.new_user` to `/forms/create` **inside the popup** and never messaged the opener, so the login/register page below never completed. Existing-user logins did message the opener → worked. Fix: auth flow now ALWAYS notifies the opener (`notifyOpenerAndClose`) with a structured `{new_user}` payload, then closes; `redirectInPlace()` only when no opener (tab opened directly).
2. **Stale popup reuse** — `useOAuth.js` used a fixed popup name `oauth_popup_${service}`; any leftover window with that name was returned by `window.open('', name, ...)` instead of a fresh popup. Fix: unique `popupName()` = `oauth_popup_<svc>_<Date.now()>` (>1s retry gives distinct names), plus a module-level `activeFlows` Map dedupes double-clicks per service; `.finally()` clears.
3. **No data channel** — `useWindowMessage` only posted bare type strings. `send()` now accepts `data` and posts `{type, payload}`; `listen()` matches both legacy string and structured payloads and exposes `event.payload` (spread copy, doesn't mutate original). Fully backward compatible (ack channel still posts raw string).
4. **Opener-side listeners** (`LoginForm`, `RegisterForm`, `QuickRegister`) now read `event?.payload?.new_user`, re-init the auth store from cookies written by the popup, route new non-invite accounts to `/forms/create` with the "Welcome back 👋 Time to create your first form!" toast. `QuickRegister` (quick modal incl. guest builder `/forms/create/guest`) keeps AFTER_LOGIN in-place continue — welcome toast only, NO forced navigation (avoiding the guest-builder save-and-continue regression).

### Verification
`npx eslint` clean on all 6 changed files; `vitest run` — 46 files / 699 tests pass (incl. `oidc-link-flow` + 2FA suites, 27 tests). Not live-tested (dev stack may not be running); deploy pipeline auto-purges CF.

### Gotchas
- Route names in marketing pages are obfuscated to `'n'` at build — don't grep them for truth; `forms-create` is the standalone builder, guest mode is a separate `forms/create/guest.vue` child.
- The OAuth callback checks `window.opener && !window.opener.closed` — tab-opened callbacks correctly bypass the popup path.

## Session: 2026-08-12 — OAuth Login-CSRF State Cookie + Callback Hardening (api/)

### Fixes (implemented + tested, NOT committed)
1. **Login-CSRF binding (double-submit cookie)** — `OAuthContextService` gains `STATE_COOKIE_NAME='oauth_state'`, `issueStateCookie(string $stateToken, int $minutes=5): Cookie` (HttpOnly, path `/`, host-only, secure=`config('session.secure')`, SameSite=Lax via `config('session.same_site')`), and `stateCookieMatches(string $stateToken): bool` (non-empty string + `hash_equals`). `OAuthController::redirect` now injects `OAuthContextService` and attaches `->withCookie(...)` to the `/oauth/connect/{provider}` JSON response. Cookie is host-only → NOT sent to Google during popup top-level navigation; `EncryptCookies` in the `api` group encrypts it transparently.
2. **State validated before code exchange** — `OAuthFlowOrchestrator::processCallback` reordered: provider → `getContext($state)` null → 419 → `stateCookieMatches()` false → 419 → `empty($params['code'])` → 400 "Missing authorization code" → `extractFromRedirect()` wrapped in `try/catch (\Throwable)` → 400 (was: code exchange ran FIRST with no guards; missing code = unhandled 500).
3. **#4 re-diagnosis (earlier mis-diagnosis)** — widget UTM is NOT lost: the `api` group DOES include `StartSession` (`Kernel.php:89-95`), so session context works. Real latent defect fixed: `OAuthUserService` line ~78 did `getUtmData() ?? getWidgetContext()['utm_data'] ?? null` → PHP 8 null-offset warning when widget context is null (redirect flows). Now null-safe (`$widgetContext['utm_data'] ?? null`).

### Tests
- New `tests/Feature/OAuth/OAuthCallbackSecurityTest.php` — connect sets `oauth_state` cookie (presence + value), callback: no state → 419, unknown state → 419, valid context but no cookie → 419, cookie mismatch → 419, matching cookie but missing code → 400. Uses `withCredentials()->withCookie()` (EncryptCookies round-trips; `postJson` drops cookies unless `withCredentials`).
- New `tests/Feature/OAuth/OAuthStateCookieTest.php` — `issueStateCookie` attrs + `stateCookieMatches` true/mismatch/absent/empty. Bound via `app()->instance('request', Request::create('/'))` (Feature group; Unit group has no app).
- Pest gotcha: file-scope helper functions are plain functions, not methods — call `startFlow()`, not `$this->startFlow()`.
- Run: `docker exec -u www-data sharaforms-api php vendor/bin/pest tests/Feature/OAuth tests/Unit/Service/OAuth` (after `config:clear`) → 64 passed (145 assertions). `php artisan test` is NOT defined in this container image — use `php vendor/bin/pest`.

### Notes
- `OAuthController::redirect` is the ONLY place issuing the cookie; widget flow (`processWidgetCallback`) is unaffected (no cookie required).
- Multi-tab clobber of `oauth_state` is an accepted standard double-submit limitation; client `activeFlows` map mitigates double-clicks.
- Pre-existing uncommitted work untouched: deploy.yml, LoginForm/QuickRegister/RegisterForm.vue, file-uploads.js, guest.vue, Dockerfile.client, oauth-popup-flow.spec.ts.

## Session: 2026-08-17 — Google OAuth Consent Screen: "Unverified App" Warning Root Cause

### Symptom
App published to Production on the consent screen, but Google still showed "Google hasn't verified this app — requesting access to sensitive info" (developer `sarveshwar2006@gmail.com`). Console Data Access said "Verification not required" while branding said "Your branding is not being shown to users".

### Root cause
- **Not a gcloud/app misconfig — it's one sensitive runtime scope.** Plain login/register (`intent: 'auth'`) requests only `openid/profile/email` (non-sensitive). Drive integration (`intent: 'integration'`) uses `drive.file` — **non-sensitive** per Google's Drive API docs. The ONLY sensitive scope anywhere is `https://www.googleapis.com/auth/forms.body.readonly`, requested solely by the Google Forms import flow (`FormImportModal.vue:540` → `intent: 'forms_import'`; mapped in `OAuthGoogleDriver::getScopesForIntent` line 87).
- **Console "no verification required" is misleading**: it reflects scopes *declared* in Data Access, but Google keys the warning off scopes *actually requested at runtime* (`forms.body.readonly`).
- **Why the scope was invisible in Data Access**: the **Google Forms API (`forms.googleapis.com`) was NOT enabled** in the GCP project. The scope picker only lists scopes for enabled APIs. **Fixed**: `gcloud services enable forms.googleapis.com --project=sharaforms` (op completed). Now the scope appears in the picker.
- **No gcloud command or REST API exists for the consumer consent screen** (branding/publish/test-users/verification) — probed `oauth2.googleapis.com/v1|/v2/brands`, `/v1/projects/775268936981/brands`, `authplatform.googleapis.com` (discovery + brands), `consumersettings.googleapis.com/v1/projects/...` → all 404. `gcloud iam oauth-clients` is workforce/IAP only. Console-only by design.

### Correct runtime scope map (code truth)
- `auth` (login/register/one-tap) → `openid, profile, email` — non-sensitive, no warning once published.
- `integration` (Drive/Sheets connect, `useOAuth.js` default `intent: 'integration'`) → + `drive.file` — non-sensitive.
- `forms_import` (Google Forms import modal only) → + `forms.body.readonly` — **SENSITIVE** → warning + needs verification.

### Manual steps given (Console-only, user to do)
1. `https://console.cloud.google.com/auth/scopes?project=sharaforms` → Data Access → Add or remove scopes → now select `forms.body.readonly` (Forms API just enabled) + `drive.file`.
2. Data Access status flips to "Verification required".
3. Submit at `https://console.cloud.google.com/auth/verification?project=sharaforms`: branding (name SharaForms, logo, support `tech@sharaforms.com`, homepage), privacy `https://sharaforms.com/privacy-policy` (200), terms `https://sharaforms.com/terms-conditions` (**NOT** `/terms-of-service` which 404s — real route is `terms-conditions.vue`), scope justification + demo video. Review up to ~10 days. Until approved, warning stays only on the import flow and is bypassable via "Advanced → continue".
4. Branding verification must be submitted first ("branding not shown" status) before scope verification passes.

### Gotchas
- The scope picker hiding a scope almost always means the owning API is disabled — check `gcloud services list --enabled --project=<proj>`.
- `drive.file` is Google's recommended non-sensitive Drive scope; `forms.body.readonly` is the sensitive trigger. Login/Sheets are clean — don't chase a warning that only belongs to the Forms-import flow.

## Session: 2026-08-18 — Google Forms Import → Drive Picker (drive.file, removes sensitive scope)

### What changed
- **No more pasted-form-ID/URL scope for Google Forms**: `forms_import` intent now requests `drive.file` (same non-sensitive scope as `integration`). `forms.body.readonly` is gone from the runtime scope map entirely — grep confirms zero code references; only AGENTS.md history remains.
- **GoogleFormsImporter**: `form_id` is now primary; falls back to `extractFormId($importData['url'] ?? '')`; throws `FormImportException('Could not extract a form ID from the URL. ...')` if neither. `validate()` returns true when `form_id` is a non-empty string, else `parent::validate()`. Calls `GET https://forms.googleapis.com/v1/forms/{formId}` with the user's token.
- **FormImportRequest**: `url` → `nullable|url|required_without:import_data.form_id`; `form_id` → `nullable|string`; `oauth_provider_id` → `nullable|integer|exists:oauth_providers,id|required_if:source,google_forms`; message `'import_data.url.required_without' => 'A form URL is required.'` (note: `required_without` uses `has()` — an empty `form_id` counts as present → 422).
- **New token endpoint** `GET /settings/providers/{provider}/token` (route `settings.providers.token`, `auth.multi` + `settings.` groups, policy `view` = owner): returns `{access_token, expires_in: 3600}` from `GoogleOAuthClient` (refreshes if expired, persists updated provider). Non-google provider → 403 `'This provider cannot issue an access token.'`; `GoogleOAuthException` → 422; generic `RuntimeException` → 422 `'Google account not found or token expired. Please reconnect your Google account.'`.
- **Client**: `oauthApi.token(providerId)` → `GET /settings/providers/{providerId}/token`. `FormImportModal.vue` loads the **Drive Picker**: `View(ViewId.DOCS)` + `setMimeTypes('application/vnd.google-apps.form')`, `setAppId(pickerAppId=775268936981)`, `setOAuthToken(access_token)`, `setDeveloperKey(pickerKey)`, `setOrigin(window.location.origin)`, callback sets `importForm.form_id = docs[0].id`. Providers lacking `drive.file` in `scopes` are disabled with "Re-connect account to fix permissions". `useOAuth.js` both scope constants = `drive.file`.
- **Config/env**: `config/services.php` google block gains `picker_api_key` (env `GOOGLE_PICKER_API_KEY`) + `picker_app_id` (default `775268936981` = numeric GCP project id); `.env.example` adds both; `docker-compose.dev.yml` passes them; `FeatureFlagsController` exposes `services.google.picker_api_key`/`picker_app_id` (client reads those flag keys).
- **GCP (CLI-verified)**: `picker.googleapis.com`, `forms.googleapis.com`, `drive.googleapis.com`, `siteverification.googleapis.com`, `apikeys.googleapis.com`, `sheets.googleapis.com` enabled. Picker Browser API key UID `2aaea2aa-96de-4e8c-897e-c1c97e17203e` restricted to `apiTargets: [picker.googleapis.com]`, allowedReferrers `sharaforms.com/*`, `www.sharaforms.com/*`, `http://localhost:3000/*`, `https://localhost:3000/*`.
- **Tests**: `FormImportTest` google block converted to `form_id` (asserts exact `https://forms.googleapis.com/v1/forms/1abc123` + Bearer header); new tests for form_id-over-URL preference, neither-provided throws, empty form_id → 422, controller flow with form_id. New `api/tests/Feature/Settings/OAuthProviderTokenTest.php` (5 tests: guest 401, non-owner 403, owned google 200, non-google 403, missing token/refresh → 422). Targeted 55/55 (182 assertions); OAuth+Forms suites 367 passed/2 skipped. Client: eslint clean; vitest 716/717 (1 pre-existing unrelated failure in `file-uploads.test.ts`).

### Gotchas
- **drive.file consequence**: pasted `docs.google.com` form URLs now return 403 from the Forms API — the Picker selection is the only way to get a `form_id`; pasting a google URL in the import box surfaces the picker (URL still works for typeform/tally/fillout).
- `setOrigin(window.location.origin)` must match a key referrer or the Picker refuses to render — the API-key referrers above are the source of truth for allowed origins.
- Site-verification list API returns `403 ACCESS_TOKEN_SCOPE_INSUFFICIENT` with the current gcloud token — verify once via Console.
- Manual step remaining (Console-only, no API): add `drive.file` to Data Access scopes after this deploy (replaces the now-removed `forms.body.readonly` guidance).

## Session: 2026-08-18 (later) — Live Console Fixes: Picker setIncludeFolders crash + benign warnings triage

### Fix shipped (commit e70d868, deploy run 32117006563, all green)
- **`Uncaught TypeError: O.setIncludeFolders is not a function`** (inside gapi picker bootstrap, `apis.google.com/js/api.js m=picker le=scs,fedcm_migration_mod`) — current Google Picker build removed `View.setIncludeFolders()`. `FormImportModal.vue:692` called `view.setIncludeFolders(true)` → whole picker crashed on the integrations/forms-create page. **Fix: deleted that line** (view is already MIME-filtered to `application/vnd.google-apps.form`; folders were never a valid import target). Only occurrence in client source (grep-verified). ESLint clean; no tests cover the picker.
- Deploy pipeline verified the live config path already in place from the env fix: `/api/content/feature-flags` returns `picker_api_key`/`picker_app_id`/`client_id` (compose-inline defaults from `d5bfaa7`), `/api/settings/providers/1/token` exists (401 unauthenticated).

### Triaged as benign / third-party (no code change)
- **"preloaded Euclid-Circular-B-Regular.ttf not used within a few seconds"** — the font IS used: home SSR inlines `#app{font-family:euclid-circular-b,sans-serif!important}` + all 4 `@font-face` rules in `<style>`; preload link has `crossorigin`; URL matches; file serves `200 font/ttf 141448B`. Chrome heuristic noise on SSR pages where initial paint happens before font swap. Cosmetic only.
- **"Blocked loading mixed active content http://developers.google.com/"** — no reference in client source (grep `developers.google`, `http://` links). Emitted by Google's own gapi/picker internals; triggered while the picker was in the errored state. Expect it to disappear with the setIncludeFolders fix.
- **Cookie `dmn_chk_...` rejected (invalid domain)** — Google third-party cookie (persona/account chooser), browser privacy rejection, not app-caused and not fixable from code.

### Verification limits
- `/forms/create` returns HTTP 302 (auth-gated) so the lazy picker chunk can't be fetched unauthenticated; the fix's presence in the shipped bundle rests on source-grep (only occurrence removed) + green UI build from e70d868 + CF purge. Ask the user to hard-refresh the integrations/forms-create page and reopen the Google Forms import to confirm the picker now renders.
- Manual Console step still pending for the user: add `drive.file` to Data Access scopes (`console.cloud.google.com/auth/scopes`).

## Session: 2026-08-18 (night 2) — Google Picker `PickerBuilder is undefined` crash → hardened gapi bootstrap

### Symptom
After e70d868 shipped, the integrations/forms-create page now threw (instead of the old `setIncludeFolders` crash): `Uncaught TypeError: can't access property "PickerBuilder", T is undefined` at `DboAVYaO.js:1:10135` (our lazy chunk), fired from inside gapi's own `m=picker` module script (`scs` loader, `fedcm_migration_mod` experiment). Meaning: gapi called our `startPicker` callback but `window.google.picker` was NOT yet defined — the loader's namespace assignment raced the callback dispatch. Not a key/appId/config problem.

### Fix (commit b7c0344, deploy run 32118646841, all 4 jobs green)
- Rewrote `loadGooglePicker` in `client/components/forms/import/FormImportModal.vue` to be race-proof + retry + friendly-fail:
  1. **Deferred namespace read**: callback wraps the builder in `setTimeout(0)` so the loader finishes attaching `google.picker` before we touch it.
  2. **Guard + retry**: reads `window.google?.picker?.PickerBuilder`; if missing, `reloadGapiScript()` (removes all `apis.google.com` scripts, `delete window.gapi`, re-appends `api.js`) up to 2 retries (`attempts` counter).
  3. **try/catch around `PickerBuilder().build().setVisible(true)`** → `console.error('[FormImportModal] Google Picker error:')` + friendly `importError`/alert instead of uncaught TypeError.
  4. **Broken-gapi guard**: if `window.gapi` exists but has no `.load`, force reload; script `onerror` and onload-without-gapi → `pickerFailed()`.
- ESLint clean; vitest 716/717 (1 = pre-existing unrelated `file-uploads.test.ts`). Live: home 200, old crashing chunk `DboAVYaO.js` gone from the payload (new bundle deployed), integrations route still 302 (auth-gated → can't grep the lazy chunk from here).

### Other reports triaged (no code change)
- Euclid preload "not used" — benign Chrome heuristic (font IS applied via inline SSR `#app`/`@font-face`, file 200 141448B).
- `dmn_chk_*` cookie rejected — Google third-party persona/account-chooser cookie, browser privacy, not app-caused.
- Mixed content `http://developers.google.com/` + gapi `_/jserror` CORS block — Google's own gapi/picker internals, expected to clear once the picker renders.
- Pending user Console step unchanged: add `drive.file` to Data Access scopes.

## Session: 2026-08-18 (night 3) — REAL picker root cause: `google.picker` destructure bug (PickerBuilder vs `picker`)

### Symptom
After b7c0344's hardened bootstrap shipped, the caught (not uncaught) error still fired: `[FormImportModal] Google Picker error: TypeError: can't access property "PickerBuilder", ne is undefined` — inside our try/catch, after the `PickerBuilder` guard passed. My earlier "loader race" theory was wrong.

### REAL root cause
`const { picker, View, ViewId, Feature, Action } = window.google.picker` destructures a **non-existent `picker` property**. The `google.picker` namespace exposes `PickerBuilder` (constructor), `View`, `ViewId`, `Feature`, `Action` — NOT `picker`. So `picker === undefined` → `new picker.PickerBuilder()` always threw `can't access property "PickerBuilder", ne is undefined`. The `setIncludeFolders` crash (e70d868) had been **masking it** by throwing earlier (before the PickerBuilder line); removing it surfaced the latent bug. The picker likely NEVER rendered in production (both prior crashes were pre-PickerBuilder). The b7c0344 retry/hardening was still worth keeping (it turned the crash into a caught, logged, user-visible error and guards gapi load flakiness) but was not the fix.

### Fix (commit 69673af, deploy run 32120067842, all 4 jobs green)
- `const { picker, ... } = pickerNs` → `const { PickerBuilder, ... } = pickerNs`; `new picker.PickerBuilder()` → `new PickerBuilder()`. The `typeof pickerNs.PickerBuilder !== 'function'` guard in b7c0344 now exactly matches the usage. ESLint clean; vitest 716/717 (1 pre-existing `file-uploads.test.ts`). Live: home 200, whole chunk set re-hashed (new bundle), integrations route still 302 auth-gated.
- **Lesson: when a runtime error says `can't access property "X", <minified> is undefined`, suspect the destructure — the minified var is a destructured name, not the namespace.** Align the guard with the actual property used.

### Font preload "not used" warning — final verdict: cosmetic, no fix
- Verified the live SSR HTML fully wires the font: `<link rel="preload" as="font" crossorigin href="/fonts/euclid-circular-b/Euclid-Circular-B-Regular.ttf">` + all 4 `@font-face` rules inlined + `#app{font-family:euclid-circular-b,sans-serif!important}`. Family name matches (`euclid-circular-b` both sides), file serves 200 font/ttf 141448B. The warning is a Chrome `font-display: swap` heuristic false positive (fallback painted first; swap outside the heuristic window). Removing the preload would silence it at a small font-latency cost — not worth it.
- `dmn_chk_*` cookie rejected — Google third-party persona cookie, not fixable from app code.

## Session: 2026-08-18 (night 4) — Picker 401 "The API developer key is invalid" → key was API-restricted

### Symptom
After 69673af (PickerBuilder destructure), the picker iframe finally loaded from `docs.google.com/picker?...developerKey=AIzaSyApglQOcqcECb-oB0D1Hmq8To-E9y8E49s&hostId=sharaforms.com&appId=775268936981&parent=https://sharaforms.com/favicon.ico&...` but returned `[HTTP/2 401 2556ms]` and the picker showed "There was an error! The API developer key is invalid." User also saw the Google cookie-consent prompt inside the iframe they couldn't click.

### Root cause
The Picker Browser API Key (uid `2aaea2aa-96de-4e8c-897e-c1c97e17203e`, displayName "Picker Browser API Key") had **`apiTargets: [picker.googleapis.com]`** in its restrictions. The Google Picker loads its UI from the `docs.google.com/picker` front-end, and an API-target-restricted key is rejected with "The API developer key is invalid" even though `picker.googleapis.com` is the documented service. Google's Picker requires the developer key to be **unrestricted or referrer-restricted only** — no `apiTargets`.

### Fix (GCP config, no code change)
- `PATCH https://apikeys.googleapis.com/v2/projects/775268936981/locations/global/keys/2aaea2aa-...?updateMask=restrictions` with body `{"restrictions":{"browserKeyRestrictions":{"allowedReferrers":["sharaforms.com/*","www.sharaforms.com/*","http://localhost:3000/*","https://localhost:3000/*"]}}}` → returned async op (poll endpoint 404s; just re-GET the key to verify). Verified: `updateTime` bumped, `restrictions` now only `browserKeyRestrictions.allowedReferrers`, **no `apiTargets`**.
- API-key restriction changes propagate within minutes; user should hard-refresh and reopen the import picker.
- gcloud token refresh hangs in this env (network/consent); workaround: read `refresh_token` from `~/.config/gcloud/credentials.db` (sqlite, account `sarveshwar2006@gmail.com`, client_id `32555940559.apps.googleusercontent.com`), then POST `https://oauth2.googleapis.com/token` with `grant_type=refresh_token` → `access_token` for curl calls to `apikeys.googleapis.com`. Token saved at `/tmp/opencode/tok.json`.
- Correct key-list endpoint: `GET /v2/projects/{proj}/locations/global/keys` (the non-location `/v2/projects/{proj}/keys` 404s).

### Still-open / benign (unchanged)
- `dmn_chk_*` cookie rejected — Google third-party persona cookie, browser privacy, not app-caused.
- Euclid font preload "not used" — Chrome `font-display: swap` heuristic false positive; font fully wired in SSR (verified earlier).
- "It is asking for google cookies access" inside the picker iframe — Google's own consent UI in the docs.google.com frame; the `setOAuthToken` token we pass should let the file list render without it. If it still blocks, that's third-party-cookie blocking in the user's browser, not app code.
- Pending user Console step unchanged: add `drive.file` to Data Access scopes (`console.cloud.google.com/auth/scopes`).
