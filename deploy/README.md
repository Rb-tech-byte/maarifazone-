# Maarifazone Deployment & CI/CD Guide (`https://maarifazone.com/`)

> **Security Notice (Public Repository):** Never commit plaintext database passwords or production API secrets to Git. Pass database passwords via terminal environment variables or single-quoted CLI flags on the server.

---

## 1. One-Command Automated Terminal Deployment (`https://maarifazone.com`)

Run these commands directly in your server terminal inside your domain web root (e.g., `/home/maarifazone.com/public_html` or `/var/www/maarifazone`):

```bash
cd /home/maarifazone.com/public_html
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

*(If you want a completely clean database wipe and fresh re-seed of all tables and demo data on first install, append `--fresh-seed --i-am-sure` to the command above.)*

### What the Automated Script Does:
1. **Fetches & Syncs Source Code**: Pulls the latest `origin/main` from `https://github.com/Rb-tech-byte/maarifazone-.git`.
2. **Detects Server PHP 8.3+**: Automatically finds `/usr/local/lsws/lsphp83/bin/php`, `lsphp84`, `lsphp85`, `ea-php83`, or `php`.
3. **Configures `.env` Safely (`chmod 600`)**: Writes `APP_URL=https://maarifazone.com`, `DB_DATABASE=maarifazone_db`, `DB_USERNAME=maarifazone_ak`, and properly quotes `DB_PASSWORD` (handling special characters like `=`, `^`, `.`).
4. **Installs Composer Dependencies**: Runs `composer install --no-dev --optimize-autoloader` (auto-downloading `composer.phar` if Composer is not installed globally).
5. **Verifies MySQL Connectivity**: Tests PDO connection (automatically switching `127.0.0.1` to `localhost` if MariaDB/CyberPanel uses local socket authentication).
6. **Migrates & Seeds Complete Data**:
   - Runs `php artisan migrate --force` and `php artisan db:seed --force`.
   - Seeds the 3-role RBAC system (`Admin`, `Instructor`, `Scholar`), demo accounts (`admin@maarifazone.com`, `instructor@maarifazone.com`, `scholar@maarifazone.com`), online courses, modular pay-per-topic demo modules (`PayPerTopicDemoSeeder`), Pesapal express checkout, and the offline Lipa Namba `61237100` (`KOMANYA LUCAS NGONZELA`) TANQR poster (`61237100_QR-2.pdf` & `61237100_QR-2.png`).
7. **Storage Symlink, Permissions & Cache Optimization**:
   - Links `public/storage -> ../storage/app/public`.
   - Removes any default `index.html` placeholder.
   - Restores vHost file ownership & permissions (`storage/`, `bootstrap/cache/`, `public/`).
   - Warms `config:cache`, `route:cache`, `view:cache`, restarts OpenLiteSpeed if present, and runs a live HTTP smoke check against `https://maarifazone.com/`.

---

## 2. Routine Subsequent Updates on the Server Terminal

Whenever new commits are pushed to `main`, run one command on the server:

```bash
cd /home/maarifazone.com/public_html
bash deploy/deploy.sh --seed
```

---

## 3. Optional: Push Local Database Snapshot & Uploaded Assets to Server over SSH

To mirror your exact local MySQL database and `storage/app/public` files directly to the server over SSH/SCP:

```bash
bash deploy/push-db-and-assets-to-server.sh \
  --ssh="root@maarifazone.com" \
  --port="22" \
  --deploy-path="/home/maarifazone.com/public_html"
```
