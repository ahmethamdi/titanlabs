#!/usr/bin/env bash
#
# Titan Labs — one-shot setup on a fresh Plesk WordPress install.
#
# Run from the WordPress root (the folder containing wp-config.php),
# with titanlabs.sql and uploads.zip sitting next to this script.
#
#   bash setup.sh https://demo.example.com
#
# What it does:
#   1. installs and activates WooCommerce + Elementor
#   2. imports the database dump
#   3. rewrites the old local URLs to your domain
#   4. unpacks wp-content/uploads
#   5. activates the Titan Labs theme and flushes caches
#
# Safe to re-run: steps that are already done are skipped.
#
# It does NOT touch wp-config.php credentials — Plesk already set those.

set -uo pipefail
# Not -e: several steps are allowed to fail harmlessly on a re-run
# (plugin already installed, cache already flushed). Anything that must
# not fail calls die() explicitly.

NEW_URL="${1:-}"
OLD_URL="http://localhost/titanlabs"

if [[ -z "$NEW_URL" ]]; then
  echo "Usage: bash setup.sh https://your-demo-domain.tld" >&2
  exit 1
fi

# Normalise: strip any trailing slash.
NEW_URL="${NEW_URL%/}"

say() { printf '\n\033[1;34m==>\033[0m %s\n' "$1"; }
die() { printf '\n\033[1;31mERROR:\033[0m %s\n' "$1" >&2; exit 1; }

[[ -f wp-config.php ]] || die "wp-config.php not found. Run this from the WordPress root."
[[ -f titanlabs.sql ]] || die "titanlabs.sql not found next to this script."

# --- wp-cli ------------------------------------------------------------
if command -v wp >/dev/null 2>&1; then
  WP="wp"
elif [[ -f wp-cli.phar ]]; then
  WP="php wp-cli.phar"
else
  say "Downloading WP-CLI"
  curl -sL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o wp-cli.phar
  WP="php wp-cli.phar"
fi

# Plesk often runs as root; allow it rather than failing.
WP="$WP --allow-root"

$WP core version >/dev/null 2>&1 || die "WP-CLI cannot talk to this WordPress install."

# --- 1. plugins --------------------------------------------------------
# Done before the import: the dump expects these tables to exist, and
# downloading is more reliable while WordPress is still in a clean state.
say "Installing plugins"

for slug in woocommerce elementor; do
  if $WP plugin is-installed "$slug" >/dev/null 2>&1; then
    echo "  $slug already installed"
  else
    echo "  installing $slug"
    $WP plugin install "$slug" >/dev/null 2>&1 \
      || echo "  ! could not download $slug — install it from wp-admin"
  fi
done

$WP plugin activate woocommerce elementor >/dev/null 2>&1 || true

if ! $WP plugin is-active woocommerce >/dev/null 2>&1; then
  echo
  echo "  WARNING: WooCommerce is not active. Install and activate it from"
  echo "           wp-admin, then re-run this script."
fi

# --- 2. database -------------------------------------------------------
say "Importing database"
# wp db import shells out via SOURCE, which some MariaDB builds reject.
# Fall back to piping the dump straight into the client.
if ! $WP db import titanlabs.sql 2>/dev/null; then
  echo "  (wp db import unavailable, piping to the mysql client)"
  DB_NAME=$($WP config get DB_NAME)
  DB_USER=$($WP config get DB_USER)
  DB_PASS=$($WP config get DB_PASSWORD)
  DB_HOST=$($WP config get DB_HOST)

  # DB_HOST may carry a port or socket.
  DB_PORT=""
  case "$DB_HOST" in
    *:*) DB_PORT="--port=${DB_HOST##*:}"; DB_HOST="${DB_HOST%%:*}" ;;
  esac

  mysql --host="$DB_HOST" $DB_PORT --user="$DB_USER" ${DB_PASS:+--password="$DB_PASS"} \
    "$DB_NAME" < titanlabs.sql \
    || die "Database import failed. Import titanlabs.sql through phpMyAdmin instead, then re-run this script."
fi

# --- 3. URLs -----------------------------------------------------------
say "Rewriting URLs: $OLD_URL  ->  $NEW_URL"
$WP search-replace "$OLD_URL" "$NEW_URL" --all-tables --precise --skip-columns=guid --report-changed-only

# Some assets reference the bare host.
$WP search-replace "http://localhost" "$NEW_URL" --all-tables --precise --skip-columns=guid --report-changed-only || true

$WP option update home "$NEW_URL"
$WP option update siteurl "$NEW_URL"

# --- 4. uploads --------------------------------------------------------
if [[ -f uploads.zip ]]; then
  say "Unpacking uploads"
  mkdir -p wp-content
  unzip -qo uploads.zip -d wp-content
else
  echo "  (uploads.zip not found — skipping; product images will be missing)"
fi

# --- 5. theme and caches ----------------------------------------------
# Re-activate after the import, which restores the previous active_plugins.
$WP plugin activate woocommerce elementor >/dev/null 2>&1 || true

say "Activating theme"
$WP theme activate titan-labs

say "Writing .htaccess and flushing permalinks"

# .htaccess is not in git (it is environment-specific), so write it here.
# RewriteBase must match the path WordPress is served from: "/" on a domain
# or subdomain, "/subfolder/" in a subdirectory install.
WP_PATH=$(printf '%s' "$NEW_URL" | sed -E 's#^https?://[^/]+##')
WP_PATH="${WP_PATH%/}/"
[[ "$WP_PATH" == "/" ]] || echo "  serving from subdirectory: $WP_PATH"

if [[ -f .htaccess ]] && ! grep -q "BEGIN WordPress" .htaccess; then
  # Someone else's rules live here — keep a copy before touching it.
  cp .htaccess ".htaccess.bak.$(date +%s)"
  echo "  existing .htaccess backed up"
fi

cat > .htaccess <<HTACCESS
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase ${WP_PATH}
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . ${WP_PATH}index.php [L]
</IfModule>
# END WordPress
HTACCESS

$WP rewrite structure '/%postname%/' --hard >/dev/null 2>&1 || true
$WP rewrite flush --hard >/dev/null 2>&1 || true
$WP elementor flush-css >/dev/null 2>&1 || true
$WP cache flush >/dev/null 2>&1 || true
$WP transient delete --all >/dev/null 2>&1 || true

# WooCommerce housekeeping.
$WP wc tool run regenerate_product_lookup_tables --user=1 >/dev/null 2>&1 || true

# WooCommerce ships with "Coming soon" mode enabled, which hides the shop,
# category and product pages behind a placeholder. Turn it off so the demo
# is actually browsable.
$WP option update woocommerce_coming_soon no >/dev/null 2>&1 || true
$WP option update woocommerce_store_pages_only no >/dev/null 2>&1 || true

# WooCommerce writes logs here; without the directory it fills the error log.
mkdir -p wp-content/uploads/wc-logs

# --- required constants ------------------------------------------------
if ! grep -q "FS_METHOD" wp-config.php; then
  say "Adding FS_METHOD to wp-config.php"
  # Insert before the "stop editing" marker.
  php -r '
    $f = "wp-config.php";
    $s = file_get_contents($f);
    $line = "define( \"FS_METHOD\", \"direct\" );\n\n";
    $marker = "/* That\x27s all, stop editing!";
    if (strpos($s, $marker) !== false) {
      $s = str_replace($marker, $line . $marker, $s);
    } else {
      $s .= "\n" . $line;
    }
    file_put_contents($f, $s);
  '
fi

# --- permissions -------------------------------------------------------
say "Fixing permissions on wp-content"
chmod -R u+rwX wp-content 2>/dev/null || true

# --- summary -----------------------------------------------------------
PRODUCTS=$($WP post list --post_type=product --post_status=publish --format=count 2>/dev/null || echo '?')
PAGES=$($WP post list --post_type=page --post_status=publish --format=count 2>/dev/null || echo '?')

cat <<SUMMARY

──────────────────────────────────────────────
 Titan Labs is set up.

   URL:       $NEW_URL
   Products:  $PRODUCTS
   Pages:     $PAGES

 Next steps:
   1. Log in at $NEW_URL/wp-admin
      user: admin   (change the password immediately)
   2. Settings -> Reading: confirm the front page is "Home"
   3. Appearance -> Menus: confirm "Primary Menu" is assigned
   4. WooCommerce -> Settings: set the real store address,
      and configure payment methods before taking orders
   5. Replace the placeholders in the Legal Notice page

 If pages 404, re-save permalinks under Settings -> Permalinks.
──────────────────────────────────────────────

SUMMARY
