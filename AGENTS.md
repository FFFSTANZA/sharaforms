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
