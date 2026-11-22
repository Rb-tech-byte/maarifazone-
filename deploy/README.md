# Maarifazone Deployment & CI/CD Guide (`https://maarifazone.com/`)

> **Security Notice (Public Repository):** Never commit plaintext database passwords or production API secrets to Git. Pass database passwords via terminal environment variables or single-quoted CLI flags on the server.

---

## 1. One-Command Automated Terminal Deployment (`https://maarifazone.com`)

Open your Ubuntu / cPanel terminal inside your domain's web root directory (`~/public_html` or your project directory) and run:

```bash
git init -b main
git remote add origin https://github.com/Rb-tech-byte/maarifazone-.git 2>/dev/null || git remote set-url origin https://github.com/Rb-tech-byte/maarifazone-.git
git fetch origin main --prune
git checkout -f -B main origin/main

bash deploy/first-time-server-setup.sh \
  --app-url="https://maarifazone.com" \
  --db-name="maarifazone_db" \
  --db-user="maarifazone_ak" \
  --db-pass='<YOUR_DB_PASSWORD>' \
  --non-interactive
```

*(If you want a clean database wipe and fresh re-seed of all tables and demo data, append `--fresh-seed --i-am-sure`.)*

### What the Automated Script Does (Zero Hardcoded Paths):
1. **Resolves Working Directory Dynamically**: Detects `APP_DIR` relative to the script itself (`$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)`), so it works in any Ubuntu or cPanel directory without hardcoded folder paths.
2. **Discovers Tools from `PATH`**: Resolves `php`, `composer`, `git`, `mysql`, and `npm` dynamically via `command -v`.
3. **Configures `.env` Safely (`chmod 600`)**: Writes `APP_URL=https://maarifazone.com`, `DB_DATABASE=maarifazone_db`, `DB_USERNAME=maarifazone_ak`, and safely quotes `DB_PASSWORD` (handling special characters like `=`, `^`, `.`).
4. **Installs Composer Dependencies**: Runs `composer install --no-dev --optimize-autoloader` (auto-downloading `composer.phar` if Composer is not installed globally).
5. **Migrates & Seeds Complete Data**:
   - Runs `php artisan migrate --force` and `php artisan db:seed --force`.
   - Seeds the 3-role RBAC system (`Admin`, `Instructor`, `Scholar`), demo accounts (`admin@maarifazone.com`, `instructor@maarifazone.com`, `scholar@maarifazone.com`), online courses, modular pay-per-topic demo modules (`PayPerTopicDemoSeeder`), Pesapal express checkout, and the offline Lipa Namba `61237100` (`KOMANYA LUCAS NGONZELA`) TANQR poster (`61237100_QR-2.pdf` & `61237100_QR-2.png`).
6. **Storage Symlink, Permissions & Cache Optimization**:
   - Links `public/storage -> ../storage/app/public`.
   - Sets writable permissions on `storage/` and `bootstrap/cache/`.
   - Warms `config:cache`, `route:cache`, `view:cache` and runs an HTTP health check against `APP_URL`.

---

## 2. Routine Subsequent Updates on the Server Terminal

From your project directory on the server:

```bash
bash deploy/deploy.sh --seed
```
