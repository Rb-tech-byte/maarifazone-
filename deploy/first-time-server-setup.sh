#!/usr/bin/env bash
# ==============================================================================
# Maarifazone First-Time Live Server Provisioning & Automated Deployment Script
# Target Live URL: https://maarifazone.com/
#
# PUBLIC REPO SAFE: Never commit real passwords or keys to Git.
# ==============================================================================
set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

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
TARGET_DIR="${TARGET_DIR:-}"
SKIP_CRON="false"
SKIP_SEED="false"
FRESH_SEED="false"
FRESH_CONFIRMED="false"
NON_INTERACTIVE="false"

for arg in "$@"; do
  case "${arg}" in
    --php=*) PHP_BIN="${arg#*=}" ;;
    --target-dir=*) TARGET_DIR="${arg#*=}" ;;
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
      sed -n '2,10p' "$0"
      exit 0
      ;;
    *)
      echo "Unknown option: ${arg}" >&2
      exit 1
      ;;
  esac
done

# If executed accidently from /root on a cPanel server, automatically relocate into the cPanel account's public_html
if [[ -n "${TARGET_DIR}" ]]; then
  APP_DIR="${TARGET_DIR}"
elif [[ "${APP_DIR}" == "/root" ]] || [[ "${APP_DIR}" == "/root/"* ]]; then
  for cpanel_docroot in /home/maarifazone/public_html /home/maarifazone.com/public_html; do
    if [[ -d "$(dirname "${cpanel_docroot}")" ]]; then
      mkdir -p "${cpanel_docroot}"
      APP_DIR="${cpanel_docroot}"
      break
    fi
  done
fi

mkdir -p "${APP_DIR}"
cd "${APP_DIR}"
echo "==> Target deployment directory: ${APP_DIR}"

# Discover genuine PHP 8.2+ CLI binary (skips cPanel cgi-fcgi wrappers that reject -r and artisan)
detect_php_bin() {
  local candidates=()
  [[ -n "${PHP_BIN:-}" ]] && candidates+=("${PHP_BIN}")
  for cmd in ea-php84 ea-php83 php8.4 php8.3 php-cli php; do
    if command -v "${cmd}" >/dev/null 2>&1; then
      candidates+=("$(command -v "${cmd}")")
    fi
  done
  for opt_bin in /opt/cpanel/ea-php84/root/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php /usr/local/bin/ea-php84 /usr/local/bin/ea-php83 /usr/local/bin/php; do
    [[ -x "${opt_bin}" ]] && candidates+=("${opt_bin}")
  done

  for bin in "${candidates[@]}"; do
    if [[ -n "${bin}" ]] && [[ -x "${bin}" ]]; then
      if "${bin}" -v 2>/dev/null | grep -q "(cli)" && "${bin}" -r 'exit(version_compare(PHP_VERSION,"8.2.0",">=")?0:1);' >/dev/null 2>&1; then
        printf '%s' "${bin}"
        return 0
      fi
    fi
  done

  for bin in "${candidates[@]}"; do
    if [[ -n "${bin}" ]] && [[ -x "${bin}" ]]; then
      if "${bin}" -r 'exit(version_compare(PHP_VERSION,"8.2.0",">=")?0:1);' >/dev/null 2>&1; then
        printf '%s' "${bin}"
        return 0
      fi
    fi
  done
  command -v php || true
}

PHP_BIN="$(detect_php_bin)"

if [[ -z "${PHP_BIN}" ]] || ! "${PHP_BIN}" -v >/dev/null 2>&1; then
  echo "ERROR: PHP CLI binary not found. Pass --php=<php-cli-binary> or set PHP_BIN." >&2
  exit 1
fi

# Ensure Git repository is initialized & fetched inside APP_DIR
if command -v git >/dev/null 2>&1; then
  git config --global --add safe.directory "${APP_DIR}" >/dev/null 2>&1 || true
  if [[ ! -d "${APP_DIR}/.git" ]]; then
    echo "==> Initializing Git repository in ${APP_DIR} and fetching ${GIT_REPO} (${BRANCH})..."
    git init -b "${BRANCH}"
    git remote add origin "${GIT_REPO}" 2>/dev/null || git remote set-url origin "${GIT_REPO}"
  else
    git remote set-url origin "${GIT_REPO}" 2>/dev/null || true
  fi
  echo "==> Syncing source code from ${GIT_REPO} (${BRANCH}) into ${APP_DIR}..."
  git fetch origin "${BRANCH}" --prune
  git checkout -f -B "${BRANCH}" "origin/${BRANCH}"
  git reset --hard "origin/${BRANCH}"
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

echo "==> Using PHP CLI binary: ${PHP_BIN}"
"${PHP_BIN}" -v
REQUIRED_EXTS=(mbstring openssl pdo pdo_mysql tokenizer xml ctype json bcmath fileinfo curl zip gd)
for ext in "${REQUIRED_EXTS[@]}"; do
  if ! "${PHP_BIN}" -m 2>/dev/null | grep -iq "^${ext}$"; then
    echo "WARNING: PHP extension '${ext}' may not be loaded." >&2
  fi
done

if [[ ! -f "${APP_DIR}/.env" ]]; then
  echo "==> Creating .env from .env.production.example..."
  cp "${APP_DIR}/.env.production.example" "${APP_DIR}/.env"
  chmod 600 "${APP_DIR}/.env"
fi

# Safe .env writer piping script via STDIN so cPanel ea_php_cli.pm never sees newlines in argv
update_env_file() {
  ENV_FILE="${APP_DIR}/.env" \
  W_APP_URL="${APP_URL}" \
  W_DB_HOST="${DB_HOST}" \
  W_DB_PORT="${DB_PORT}" \
  W_DB_NAME="${DB_NAME}" \
  W_DB_USER="${DB_USER}" \
  W_DB_PASS="${DB_PASS}" \
  W_PSP_KEY="${PESAPAL_KEY}" \
  W_PSP_SEC="${PESAPAL_SECRET}" \
  W_PSP_IPN="${PESAPAL_IPN}" \
  W_PSP_MODE="${PESAPAL_MODE}" \
  "${PHP_BIN}" <<'EOPHP'
<?php
$file = getenv('ENV_FILE');
$content = file_exists($file) ? file_get_contents($file) : '';
$appUrl = (string) getenv('W_APP_URL');
$updates = [
    'APP_NAME' => 'Maarifazone',
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_URL' => $appUrl,
    'APP_TIMEZONE' => 'Africa/Dar_es_Salaam',
    'APP_CURRENCY' => 'TZS',
    'CACHE_STORE' => 'file',
    'SESSION_SECURE_COOKIE' => str_starts_with($appUrl, 'https://') ? 'true' : 'false',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => (string) getenv('W_DB_HOST'),
    'DB_PORT' => (string) getenv('W_DB_PORT'),
    'DB_DATABASE' => (string) getenv('W_DB_NAME'),
    'DB_USERNAME' => (string) getenv('W_DB_USER'),
    'DB_PASSWORD' => (string) getenv('W_DB_PASS'),
    'PESAPAL_MODE' => (string) getenv('W_PSP_MODE') ?: 'sandbox',
];
if (getenv('W_PSP_KEY') !== false && getenv('W_PSP_KEY') !== '') {
    $updates['PESAPAL_CONSUMER_KEY'] = (string) getenv('W_PSP_KEY');
}
if (getenv('W_PSP_SEC') !== false && getenv('W_PSP_SEC') !== '') {
    $updates['PESAPAL_CONSUMER_SECRET'] = (string) getenv('W_PSP_SEC');
}
if (getenv('W_PSP_IPN') !== false && getenv('W_PSP_IPN') !== '') {
    $updates['PESAPAL_IPN_ID'] = (string) getenv('W_PSP_IPN');
}
foreach ($updates as $key => $val) {
    $str = (string) $val;
    $needsQuotes = $str !== '' && (preg_match('/[\s#"\'\\\$!=^]/', $str) === 1);
    $formatted = $needsQuotes
        ? '"' . str_replace(['\\', '"', '$'], ['\\\\', '\"', '\$'], $str) . '"'
        : $str;
    $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
    $line = $key . '=' . $formatted;
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, str_replace(['\\', '$'], ['\\\\', '\$'], $line), $content, 1);
    } else {
        $content = rtrim($content) . PHP_EOL . $line . PHP_EOL;
    }
}
file_put_contents($file, $content);
EOPHP
}

update_env_file
chmod 600 "${APP_DIR}/.env"
echo "==> Wrote production database & app credentials into ${APP_DIR}/.env (chmod 600)."

# Prepare storage & cache folders and ensure Composer autoloader exists for key:generate
mkdir -p storage/framework/{cache/data,sessions,views,testing} storage/logs storage/app/public bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [[ ! -f "${APP_DIR}/vendor/autoload.php" ]]; then
  export COMPOSER_ALLOW_SUPERUSER=1
  COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer || true)}"
  if [[ ! -f "${APP_DIR}/composer.phar" ]]; then
    echo "==> Downloading composer.phar locally..."
    "${PHP_BIN}" -r 'copy("https://getcomposer.org/installer","composer-setup.php");'
    "${PHP_BIN}" composer-setup.php --quiet
    rm -f composer-setup.php
  fi
  if [[ -f "${APP_DIR}/composer.phar" ]]; then
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

# Verify MySQL connection via PHP PDO (auto-switches 127.0.0.1 -> localhost if cPanel/MariaDB socket auth is required)
echo "==> Verifying MySQL connection to ${DB_USER}@${DB_HOST}:${DB_PORT}/${DB_NAME}..."
test_pdo_conn() {
  T_HOST="$1" T_PORT="${DB_PORT}" T_DB="${DB_NAME}" T_USER="${DB_USER}" T_PASS="${DB_PASS}" "${PHP_BIN}" <<'EOPHP'
<?php
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', getenv('T_HOST'), getenv('T_PORT'), getenv('T_DB'));
new PDO($dsn, (string) getenv('T_USER'), (string) getenv('T_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
EOPHP
}

if ! test_pdo_conn "${DB_HOST}" 2>/dev/null; then
  if [[ "${DB_HOST}" == "127.0.0.1" ]] && test_pdo_conn "localhost" 2>/dev/null; then
    echo "==> Switched DB_HOST to 'localhost' for cPanel/MariaDB socket auth."
    DB_HOST="localhost"
    update_env_file
  else
    test_pdo_conn "${DB_HOST}"
    echo "Ensure database '${DB_NAME}' exists and user '${DB_USER}' has ALL PRIVILEGES on it in cPanel." >&2
    exit 1
  fi
fi
echo "MySQL connection verified successfully."

DEPLOY_ARGS=(--no-git --branch="${BRANCH}" --php="${PHP_BIN}")
[[ -n "${SQL_DUMP}" ]] && DEPLOY_ARGS+=(--with-sql="${SQL_DUMP}")
if [[ "${FRESH_SEED}" == "true" ]]; then
  DEPLOY_ARGS+=(--fresh-seed)
  [[ "${FRESH_CONFIRMED}" == "true" ]] && DEPLOY_ARGS+=(--i-am-sure)
elif [[ "${SKIP_SEED}" != "true" ]]; then
  DEPLOY_ARGS+=(--seed)
fi

echo "==> Running ${APP_DIR}/deploy/deploy.sh ${DEPLOY_ARGS[*]} ..."
bash "${APP_DIR}/deploy/deploy.sh" "${DEPLOY_ARGS[@]}"

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
echo " Deployed Directory : ${APP_DIR}"
echo " Live URL           : ${APP_URL}"
echo " Database           : ${DB_NAME} (User: ${DB_USER})"
echo " Seeded Admin       : admin@maarifazone.com / password"
echo " Seeded Instructor  : instructor@maarifazone.com / password"
echo " Seeded Scholar     : scholar@maarifazone.com / password"
echo " Lipa Namba / TANQR : 61237100 (KOMANYA LUCAS NGONZELA)"
echo "======================================================================"
