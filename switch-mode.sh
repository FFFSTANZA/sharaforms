#!/usr/bin/env bash
#
# switch-mode.sh — flip the whole codebase between development and production mode.
#
# What it does:
#   * Swaps api/.env and client/.env between gitignored per-mode templates
#     (api/.env.development / api/.env.production, client/.env.development /
#     client/.env.production).
#   * BEFORE switching it snapshots the CURRENT .env files into the template of
#     the CURRENT mode, so toggling back and forth never loses secrets.
#   * When the target template is missing it generates one from the current
#     .env by flipping only the known mode-sensitive keys and inheriting every
#     other value verbatim (secrets are preserved, not invented).
#   * Every overwrite is backed up to <file>.env.bak.<timestamp>.
#
# Usage:
#   ./switch-mode.sh            show current mode + usage
#   ./switch-mode.sh status     show current mode for api and client
#   ./switch-mode.sh dev        switch api + client to development mode
#   ./switch-mode.sh prod       switch api + client to production mode
#   ./switch-mode.sh snapshot   capture current .env files as the template of
#                               the detected current mode (explicit snapshot)
#
# Secrets: all templates match the existing .gitignore rules (.env.* is
# ignored except .env.example), so nothing here can leak into git.
#
# Gotcha: the API container bakes a config cache at start; after switching,
# run `docker exec -u www-data sharaforms-api php artisan config:clear`.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
API_ENV="$ROOT/api/.env"
CLIENT_ENV="$ROOT/client/.env"
TS="$(date +%Y%m%d-%H%M%S)"

# ---------------------------------------------------------------------------
# Mode-sensitive keys — everything else is inherited as-is when generating a
# missing template. Format: KEY|development value|production value
# ---------------------------------------------------------------------------
API_MODE_KEYS=(
  "APP_ENV|local|production"
  "APP_DEBUG|true|false"
  "APP_URL|http://localhost|https://sharaforms.com"
  "FRONT_URL|http://localhost:3000|https://sharaforms.com"
  "DODO_PAYMENTS_ENVIRONMENT|test_mode|live_mode"
  "JWT_SKIP_IP_UA_VALIDATION|true|false"
  "FILESYSTEM_DRIVER|local|r2"
  "MAIL_MAILER|log|smtp"
  "CORS_ALLOWED_ORIGINS|http://localhost:3000|https://sharaforms.com"
  "SESSION_SECURE_COOKIE|false|true"
  "WEBHOOKS_ALLOW_PRIVATE_URLS|true|false"
)

CLIENT_MODE_KEYS=(
  "NUXT_PUBLIC_ENV|development|production"
  "NUXT_PUBLIC_APP_URL|http://localhost:3000|https://sharaforms.com"
  "NUXT_PUBLIC_API_BASE|http://localhost|/api"
  "NUXT_PRIVATE_API_BASE|http://sharaforms-ingress|http://ingress/api"
  "NUXT_PUBLIC_GOOGLE_ANALYTICS_CODE||G-RYMDPF5CTW"
)

SUFFIX_DEV="development"
SUFFIX_PROD="production"

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------
print_usage() {
  sed -n '2,18p' "$0" | sed 's/^# \{0,1\}//'
}

# Detect the current mode of one .env file. Prints development|production|unknown.
detect_mode() {
  local file="$1"
  if [[ ! -f "$file" ]]; then
    echo "unknown"
    return
  fi
  local app_env
  app_env="$(grep -E '^APP_ENV=' "$file" 2>/dev/null | head -n1 | cut -d= -f2- || true)"
  case "$app_env" in
    local) echo "development" ;;
    production) echo "production" ;;
    *)
      local public_env
      public_env="$(grep -E '^NUXT_PUBLIC_ENV=' "$file" 2>/dev/null | head -n1 | cut -d= -f2- || true)"
      case "$public_env" in
        development) echo "development" ;;
        production) echo "production" ;;
        *) echo "unknown" ;;
      esac
      ;;
  esac
}

# Flip the mode-sensitive keys of a copy of $file and write the result to $template.
generate_template() {
  local file="$1" mode="$2" template="$3" map_name="$4"
  local -n keys="$map_name"
  local tmp="$template.tmp.$$"
  cp "$file" "$tmp"
  for entry in "${keys[@]}"; do
    local key devval prodval target
    IFS='|' read -r key devval prodval <<< "$entry"
    if [[ "$mode" == "development" ]]; then
      target="$devval"
    else
      target="$prodval"
    fi
    if grep -qE "^${key}=" "$tmp"; then
      # '|' is the sed delimiter — safe for URLs like https://...
      sed -i "s|^${key}=.*|${key}=${target}|" "$tmp"
    else
      printf '%s=%s\n' "$key" "$target" >> "$tmp"
    fi
  done
  mv "$tmp" "$template"
  echo "    generated $template (based on current values, ${mode} overrides applied)"
}

# Switch a single .env file to the target mode.
switch_file() {
  local file="$1" target_suffix="$2" current_suffix="$3" map_name="$4"
  local cur_template="${file}.${current_suffix}"
  local tgt_template="${file}.${target_suffix}"
  local target_mode
  if [[ "$target_suffix" == "$SUFFIX_DEV" ]]; then target_mode="development"; else target_mode="production"; fi

  # 1. Preserve the current state into the current mode's template.
  cp "$file" "$cur_template"
  echo "  snapshotted $file -> $cur_template"

  # 2. Ensure the target template exists.
  if [[ ! -f "$tgt_template" ]]; then
    echo "  no $tgt_template yet — generating from current values"
    generate_template "$file" "$target_mode" "$tgt_template" "$map_name"
    if [[ "$target_mode" == "production" ]]; then
      echo "    WARNING: production template was derived from current (non-production) values."
      echo "    Any secrets that differ between modes (Dodo live key, DB creds, R2, JWT) are"
      echo "    still the DEV ones. Fix them in $tgt_template or restore a known-good prod .env"
      echo "    and run: ./switch-mode.sh snapshot"
    fi
  fi

  # 3. Backup + swap.
  cp "$file" "$file.bak.$TS"
  cp "$tgt_template" "$file"
  echo "  switched $file -> ${target_mode} (backup: $file.bak.$TS)"
}

status() {
  echo "Current mode:"
  printf '  api    : %s   (APP_ENV=%s)\n' \
    "$(detect_mode "$API_ENV")" \
    "$(grep -E '^APP_ENV=' "$API_ENV" 2>/dev/null | head -n1 | cut -d= -f2- || echo '-')"
  printf '  client : %s   (NUXT_PUBLIC_ENV=%s)\n' \
    "$(detect_mode "$CLIENT_ENV")" \
    "$(grep -E '^NUXT_PUBLIC_ENV=' "$CLIENT_ENV" 2>/dev/null | head -n1 | cut -d= -f2- || echo '-')"
  echo
  echo "Templates:"
  for f in "$API_ENV.development" "$API_ENV.production" "$CLIENT_ENV.development" "$CLIENT_ENV.production"; do
    [[ -f "$f" ]] && printf '  %-60s exists\n' "${f#$ROOT/}" || printf '  %-60s missing\n' "${f#$ROOT/}"
  done
}

next_steps() {
  local target_mode="$1"
  if [[ "$target_mode" == "development" ]]; then
    cat <<'EOF'

Next steps (development):
  docker compose -f docker-compose.dev.yml up -d      # full dev stack
  docker compose -f docker-compose.dev.yml restart    # if already running
  # Standalone (no docker): cd client && npm run dev | cd api && php artisan serve
  # Note: the dev compose 'environment:' blocks override most of these values anyway.
EOF
  else
    cat <<'EOF'

Next steps (production):
  docker compose up -d                                # full prod stack
  docker compose restart                              # if already running
  docker exec -u www-data sharaforms-api php artisan config:clear   # REQUIRED — config cache is baked at container start
EOF
  fi
}

# ---------------------------------------------------------------------------
# Commands
# ---------------------------------------------------------------------------
[[ -f "$API_ENV" ]] || { echo "ERROR: $API_ENV not found. Run from the repo root." >&2; exit 1; }
[[ -f "$CLIENT_ENV" ]] || { echo "ERROR: $CLIENT_ENV not found. Run from the repo root." >&2; exit 1; }

CMD="${1:-status}"
case "$CMD" in
  status)
    status
    exit 0
    ;;
  dev|development)
    TARGET="$SUFFIX_DEV"; TARGET_MODE="development"
    ;;
  prod|production)
    TARGET="$SUFFIX_PROD"; TARGET_MODE="production"
    ;;
  snapshot)
    CUR_API="$(detect_mode "$API_ENV")"
    [[ "$CUR_API" == "unknown" ]] && { echo "ERROR: cannot detect api mode from $API_ENV" >&2; exit 1; }
    cp "$API_ENV" "$API_ENV.$CUR_API"
    cp "$CLIENT_ENV" "$CLIENT_ENV.$CUR_API"
    echo "Snapshotted current .env files as ${CUR_API} templates."
    exit 0
    ;;
  -h|--help|help)
    print_usage
    exit 0
    ;;
  *)
    echo "Unknown command: $CMD" >&2
    print_usage
    exit 1
    ;;
esac

# Detect both current modes.
CUR_API="$(detect_mode "$API_ENV")"
CUR_CLIENT="$(detect_mode "$CLIENT_ENV")"
if [[ "$CUR_API" == "unknown" || "$CUR_CLIENT" == "unknown" ]]; then
  echo "ERROR: cannot detect current mode (api=$CUR_API, client=$CUR_CLIENT)." >&2
  echo "Run ./switch-mode.sh snapshot after setting APP_ENV/NUXT_PUBLIC_ENV manually." >&2
  exit 1
fi

echo "Current: api=$CUR_API, client=$CUR_CLIENT"
echo "Target : $TARGET_MODE"
echo

if [[ "$CUR_API" == "$TARGET_MODE" && "$CUR_CLIENT" == "$TARGET_MODE" ]]; then
  echo "Already in ${TARGET_MODE} mode — nothing to do."
  next_steps "$TARGET_MODE"
  exit 0
fi

if [[ "$CUR_API" != "$TARGET_MODE" ]]; then
  echo "Switching api..."
  switch_file "$API_ENV" "$TARGET" "$CUR_API" API_MODE_KEYS
fi
if [[ "$CUR_CLIENT" != "$TARGET_MODE" ]]; then
  echo "Switching client..."
  switch_file "$CLIENT_ENV" "$TARGET" "$CUR_CLIENT" CLIENT_MODE_KEYS
fi

echo
echo "Done — codebase is now in ${TARGET_MODE} mode."
next_steps "$TARGET_MODE"