#!/usr/bin/env bash
# ==============================================================================
# Maarifazone First-Time Live Server Provisioning & Automated Deployment Script
# Target Live URL: https://maarifazone.com/
#
# PUBLIC REPO SAFE: Never commit real passwords or keys to Git.
# Run directly on the live server over SSH:
#   DB_PASSWORD='<YOUR_DB_PASSWORD>' bash deploy/first-time-server-setup.sh
# Or with explicit flags (use single quotes around password so bash does not expand special chars):
#   bash deploy/first-time-server-setup.sh \
#     --app-url="https://maarifazone.com" \
#     --db-name="maarifazone_db" \
#     --db-user="maarifazone_ak" \
#     --db-pass='<YOUR_DB_PASSWORD>'
# ==============================================================================
set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${APP_DIR}"

detect_php_bin() {
  for candidate in \
    "${PHP_BIN:-}" \
    "$(command -v php || true)" \
    /usr/local/lsws/lsphp85/bin/php \
    /usr/local/lsws/lsphp84/bin/php \
    /usr/local/lsws/lsphp83/bin/php \
    /usr/local/bin/ea-php83 \
    /usr/bin/php8.3; do
    if [[ -n "${candidate}" ]] && [[ -x "${candidate}" ]]; then
      if "${candidate}" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' >/dev/null 2>&1; then
        printf '%s' "${candidate}"
        return 0
      fi
    fi
  done
  command -v php || true
}

PHP_BIN="$(detect_php_bin)"
GIT_REPO="${GIT_REPO:-https://github.com/Rb-tech-byte/maarifazone-.git}"
BRANCH="${DEPLOY_BRANCH:-main}"
APP_URL="${APP_URL:-https://maarifazone.com}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_DATABASE:-${DB_NAME:-maarifazone_db}}"
DB_USER="${DB_USERNAME:-${DB_USER:-maarifazone_ak}}"
DB_PASS="${DB_PASSWORD:-${DB_PASS:-}}"
PESAPAL_KEY="${PESAPAL_CONSUMER_KEY:-}"
PESAPAL_SECRET="${PESAPAL_CONSUMER_SECRET:-}"
PESAPAL_IPN="${PESAPAL_IPN_ID:-}"
PESAPAL_MODE="${PESAPAL_MODE:-sandbox}"
SQL_DUMP="${SQL_DUMP:-}"
SKIP_CRON="false"
SKIP_SEED="false"
FRESH_SEED="false"
FRESH_CONFIRMED="false"
NON_INTERACTIVE="false"

for arg in "$@"; do
  case "${arg}" in
    --php=*) PHP_BIN="${arg#*=}" ;;
    --repo=*) GIT_REPO="${arg#*=}" ;;
    --branch=*) BRANCH="${arg#*=}" ;;
    --app-url=*) APP_URL="${arg#*=}" ;;
    --db-host=*) DB_HOST="${arg#*=}" ;;
    --db-port=*) DB_PORT="${arg#*=}" ;;
    --db-name=*) DB_NAME="${arg#*=}" ;;
    --db-user=*) DB_USER="${arg#*=}" ;;
    --db-pass=*) DB_PASS="${arg#*=}" ;;
    --pesapal-key=*) PESAPAL_KEY="${arg#*=}" ;;
    --pesapal-secret=*) PESAPAL_SECRET="${arg#*=}" ;;
    --pesapal-ipn=*) PESAPAL_IPN="${arg#*=}" ;;
    --pesapal-mode=*) PESAPAL_MODE="${arg#*=}" ;;
    --with-sql=*) SQL_DUMP="${arg#*=}" ;;
    --fresh-seed) FRESH_SEED="true" ;;
    --i-am-sure) FRESH_CONFIRMED="true" ;;
    --skip-cron) SKIP_CRON="true" ;;
    --skip-seed) SKIP_SEED="true" ;;
    --non-interactive) NON_INTERACTIVE="true" ;;
    -h|--help)
      sed -n '2,15p' "$0"
      exit 0
      ;;
    *)
      echo "Unknown option: ${arg}" >&2
      exit 1
      ;;
  esac
done

if [[ -z "${PHP_BIN}" ]] || ! command -v "${PHP_BIN}" >/dev/null 2>&1; then
  echo "ERROR: PHP binary not found. Pass --php=/path/to/php (e.g., /usr/local/lsws/lsphp83/bin/php)." >&2
  exit 1
fi

# Ensure Git repository is initialized & fetched if running in a newly created web root
if [[ ! -d "${APP_DIR}/.git" ]] && command -v git >/dev/null 2>&1; then
  echo "==> Initializing Git repository and fetching ${GIT_REPO} (${BRANCH})..."
  git config --global --add safe.directory "${APP_DIR}" >/dev/null 2>&1 || true
  git init -b "${BRANCH}"
  git remote add origin "${GIT_REPO}" 2>/dev/null || git remote set-url origin "${GIT_REPO}"
  git fetch origin "${BRANCH}" --prune
  git checkout -f -B "${BRANCH}" "origin/${BRANCH}"
fi

# Interactive prompt when running in terminal & password was not pre-supplied
if [[ "${NON_INTERACTIVE}" != "true" ]] && [[ -t 0 ]]; then
  echo "=== Maarifazone Server Environment Setup (${APP_URL}) ==="
  if [[ -z "${DB_NAME}" ]]; then
    read -r -p "MySQL Database Name [maarifazone_db]: " DB_NAME
    DB_NAME="${DB_NAME:-maarifazone_db}"
  fi
  if [[ -z "${DB_USER}" ]]; then
    read -r -p "MySQL Database User [maarifazone_ak]: " DB_USER
    DB_USER="${DB_USER:-maarifazone_ak}"
  fi
  if [[ -z "${DB_PASS}" ]]; then
    read -r -s -p "MySQL Database Password for ${DB_USER} (hidden): " DB_PASS
    echo ""
  fi
fi

if [[ -z "${DB_NAME}" ]] || [[ -z "${DB_USER}" ]]; then
  echo "ERROR: Database name and username are required. Pass --db-name/--db-user or run interactively." >&2
  exit 1
fi

echo "==> Checking PHP runtime & required extensions (PHP 8.3+)..."
"${PHP_BIN}" -v
REQUIRED_EXTS=(mbstring openssl pdo pdo_mysql tokenizer xml ctype json bcmath fileinfo curl zip gd)
for ext in "${REQUIRED_EXTS[@]}"; do
  if ! "${PHP_BIN}" -m | grep -iq "^${ext}$"; then
    echo "WARNING: PHP extension '${ext}' may not be loaded." >&2
  fi
done

if [[ ! -f "${APP_DIR}/.env" ]]; then
  echo "==> Creating .env from .env.production.example..."
  cp "${APP_DIR}/.env.production.example" "${APP_DIR}/.env"
  chmod 600 "${APP_DIR}/.env"
fi

# Safe .env writer using PHP so special characters (=, ^, ., !, $, #, &, spaces) never corrupt .env
write_env_vars() {
  ENV_FILE="${APP_DIR}/.env" "${PHP_BIN}" -r '
    $file = getenv("ENV_FILE");
    $content = file_get_contents($file);
    $updates = json_decode(stream_get_contents(STDIN), true);
    foreach ($updates as $key => $val) {
        if ($val === null) continue;
        $str = (string) $val;
        $needsQuotes = $str !== "" && (preg_match("/[\s#\"'\''\\\\\$!=^]/", $str) === 1);
        $formatted = $needsQuotes
            ? "\"" . str_replace(["\\", "\"", "\$"], ["\\\\", "\\\"", "\\\$"], $str) . "\""
            : $str;
        $pattern = "/^" . preg_quote($key, "/") . "=.*$/m";
        $line = $key . "=" . $formatted;
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, str_replace(["\\", "\$"], ["\\\\", "\\\$"], $line), $content, 1);
        } else {
            $content = rtrim($content) . PHP_EOL . $line . PHP_EOL;
        }
    }
    file_put_contents($file, $content);
  '
}

JSON_PAYLOAD="$("${PHP_BIN}" -r '
  echo json_encode(array_filter([
    "APP_NAME" => "Maarifazone",
    "APP_ENV" => "production",
    "APP_DEBUG" => "false",
    "APP_URL" => $argv[1],
    "APP_TIMEZONE" => "Africa/Dar_es_Salaam",
    "APP_CURRENCY" => "TZS",
    "CACHE_STORE" => "file",
    "SESSION_SECURE_COOKIE" => str_starts_with($argv[1], "https://") ? "true" : "false",
    "DB_CONNECTION" => "mysql",
    "DB_HOST" => $argv[2],
    "DB_PORT" => $argv[3],
    "DB_DATABASE" => $argv[4],
    "DB_USERNAME" => $argv[5],
    "DB_PASSWORD" => $argv[6],
    "PESAPAL_CONSUMER_KEY" => $argv[7] !== "" ? $argv[7] : null,
    "PESAPAL_CONSUMER_SECRET" => $argv[8] !== "" ? $argv[8] : null,
    "PESAPAL_IPN_ID" => $argv[9] !== "" ? $argv[9] : null,
    "PESAPAL_MODE" => $argv[10],
  ], fn($v) => $v !== null));
' "${APP_URL}" "${DB_HOST}" "${DB_PORT}" "${DB_NAME}" "${DB_USER}" "${DB_PASS}" "${PESAPAL_KEY}" "${PESAPAL_SECRET}" "${PESAPAL_IPN}" "${PESAPAL_MODE}")"

printf '%s' "${JSON_PAYLOAD}" | write_env_vars
chmod 600 "${APP_DIR}/.env"
echo "==> Wrote production database & app credentials into .env (chmod 600)."

# Prepare storage & cache folders and ensure Composer autoloader exists for key:generate
mkdir -p storage/framework/{cache/data,sessions,views,testing} storage/logs storage/app/public bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [[ ! -f "${APP_DIR}/vendor/autoload.php" ]]; then
  export COMPOSER_ALLOW_SUPERUSER=1
  COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer || true)}"
  if [[ -z "${COMPOSER_BIN}" ]] && [[ ! -f "${APP_DIR}/composer.phar" ]]; then
    echo "==> Composer not found globally; downloading composer.phar locally..."
    "${PHP_BIN}" -r 'copy("https://getcomposer.org/installer", "composer-setup.php");'
    "${PHP_BIN}" composer-setup.php --quiet
    rm -f composer-setup.php
  fi
  if [[ -f "${APP_DIR}/composer.phar" ]] && [[ -z "${COMPOSER_BIN}" ]]; then
    echo "==> Installing Composer production dependencies via composer.phar..."
    CACHE_STORE=array "${PHP_BIN}" "${APP_DIR}/composer.phar" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
  elif [[ -n "${COMPOSER_BIN}" ]]; then
    echo "==> Installing Composer production dependencies..."
    CACHE_STORE=array "${PHP_BIN}" "$(command -v "${COMPOSER_BIN}")" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
  else
    echo "ERROR: composer not found. Install composer before running setup." >&2
    exit 1
  fi
fi

if ! grep -qE '^APP_KEY=base64:' "${APP_DIR}/.env"; then
  echo "==> Generating Laravel APP_KEY..."
  CACHE_STORE=array "${PHP_BIN}" artisan key:generate --force --no-interaction
fi

# Verify MySQL connection via PHP PDO before running migrations (falls back 127.0.0.1 -> localhost if CyberPanel socket-only user)
echo "==> Verifying MySQL connection to ${DB_USER}@${DB_HOST}:${DB_PORT}/${DB_NAME}..."
if ! "${PHP_BIN}" -r '
  $host = $argv[1]; $port = $argv[2]; $db = $argv[3]; $user = $argv[4]; $pass = $argv[5];
  new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
' "${DB_HOST}" "${DB_PORT}" "${DB_NAME}" "${DB_USER}" "${DB_PASS}" 2>/dev/null; then
  if [[ "${DB_HOST}" == "127.0.0.1" ]] && "${PHP_BIN}" -r '
    $db = $argv[1]; $user = $argv[2]; $pass = $argv[3];
    new PDO("mysql:host=localhost;dbname={$db};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  ' "${DB_NAME}" "${DB_USER}" "${DB_PASS}" 2>/dev/null; then
    echo "==> Switched DB_HOST to 'localhost' for CyberPanel/MariaDB socket auth."
    DB_HOST="localhost"
    printf '{"DB_HOST":"localhost"}' | write_env_vars
  else
    "${PHP_BIN}" -r '
      $host = $argv[1]; $port = $argv[2]; $db = $argv[3]; $user = $argv[4]; $pass = $argv[5];
      try {
        new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
      } catch (Throwable $e) {
        fwrite(STDERR, "ERROR: MySQL connection failed: " . $e->getMessage() . "\n");
        exit(1);
      }
    ' "${DB_HOST}" "${DB_PORT}" "${DB_NAME}" "${DB_USER}" "${DB_PASS}"
    echo "Ensure database '${DB_NAME}' exists and user '${DB_USER}' has ALL PRIVILEGES on it." >&2
    exit 1
  fi
fi
echo "MySQL connection verified successfully."

DEPLOY_ARGS=(--branch="${BRANCH}" --php="${PHP_BIN}")
[[ -n "${SQL_DUMP}" ]] && DEPLOY_ARGS+=(--with-sql="${SQL_DUMP}")
if [[ "${FRESH_SEED}" == "true" ]]; then
  DEPLOY_ARGS+=(--fresh-seed)
  [[ "${FRESH_CONFIRMED}" == "true" ]] && DEPLOY_ARGS+=(--i-am-sure)
elif [[ "${SKIP_SEED}" != "true" ]]; then
  DEPLOY_ARGS+=(--seed)
fi

echo "==> Running deploy/deploy.sh ${DEPLOY_ARGS[*]} ..."
bash "${SCRIPT_DIR}/deploy.sh" "${DEPLOY_ARGS[@]}"

if [[ "${SKIP_CRON}" != "true" ]] && command -v crontab >/dev/null 2>&1; then
  CRON_LINE="* * * * * cd ${APP_DIR} && ${PHP_BIN} artisan schedule:run >> /dev/null 2>&1"
  EXISTING_CRON="$(crontab -l 2>/dev/null || true)"
  if ! printf '%s\n' "${EXISTING_CRON}" | grep -Fq "${APP_DIR} && ${PHP_BIN} artisan schedule:run"; then
    (printf '%s\n' "${EXISTING_CRON}"; printf '%s\n' "${CRON_LINE}") | awk 'NF' | crontab - || true
    echo "==> Installed Laravel scheduler cron job."
  fi
fi

echo ""
echo "======================================================================"
echo " Maarifazone Live Deployment & Seeding Complete!"
echo " Live URL           : ${APP_URL}"
echo " Database           : ${DB_NAME} (User: ${DB_USER})"
echo " Seeded Admin       : admin@maarifazone.com / password"
echo " Seeded Instructor  : instructor@maarifazone.com / password"
echo " Seeded Scholar     : scholar@maarifazone.com / password"
echo " Lipa Namba / TANQR : 61237100 (KOMANYA LUCAS NGONZELA)"
echo "======================================================================"
