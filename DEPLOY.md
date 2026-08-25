# Deploying to Plesk

How this site is put on a server, and how theme changes reach it afterwards.

## How the pieces split

| What | Where it lives | How it gets updated |
| --- | --- | --- |
| Theme (`wp-content/themes/titan-labs`) | **This repo** | `git push` → Plesk pulls automatically |
| Page source HTML (`content/`) | **This repo** | Only re-imported deliberately (see warning below) |
| WordPress core, WooCommerce, Elementor | Server only | WordPress admin, one click |
| Products, pages, orders, settings | Database | Lives on the server; never in git |
| Product images (`wp-content/uploads`) | Server only | Uploaded through wp-admin |

The database is the reason a `git clone` alone does not give you a working
site: every product, page and setting lives there, not in the repo. The
one-time setup below moves it across; after that, only the theme travels
through git.

---

## First-time setup

You need the deployment bundle: `titanlabs.sql`, `uploads.zip` and `setup.sh`.

### 1. Install WordPress in Plesk

**Websites & Domains → WordPress → Install.** Use the demo subdomain
(e.g. `demo.titanlabs.eu`). Let Plesk create the database. Nothing else to
configure — the next step overwrites the content anyway.

### 2. Connect the repo

**Websites & Domains → Git → Add Repository**

- Repository URL: `git@github.com:ahmethamdi/titanlabs.git`
- Copy the SSH public key Plesk shows you into GitHub:
  **repo → Settings → Deploy keys → Add deploy key** (read-only is enough)
- Deployment path: the domain's document root (usually `httpdocs`)
- Deployment mode: **Automatic** — Plesk then pulls on every push

Because the repo mirrors the WordPress folder structure, the theme lands in
`httpdocs/wp-content/themes/titan-labs/` on its own.

> Plesk also prints a **webhook URL**. Add it in GitHub under
> **Settings → Webhooks** so pushes deploy within seconds instead of waiting
> for Plesk's polling interval.

### 3. Run the setup script

Upload `titanlabs.sql`, `uploads.zip` and `setup.sh` into the document root,
then over SSH:

```bash
cd ~/httpdocs
bash setup.sh https://demo.titanlabs.eu
```

It imports the database, rewrites the local URLs to your domain, unpacks the
uploads, installs WooCommerce and Elementor, activates the theme, and flushes
caches. Takes a couple of minutes.

No SSH access? Do it by hand:

1. Import `titanlabs.sql` through **Plesk → Databases → phpMyAdmin**
2. Unzip `uploads.zip` into `httpdocs/wp-content/`
3. Install and activate WooCommerce and Elementor from wp-admin
4. Activate the Titan Labs theme
5. Fix the URLs — the database still points at `http://localhost/titanlabs`.
   Use the **Better Search Replace** plugin: search
   `http://localhost/titanlabs`, replace with your domain, run on all tables.
   A plain SQL find-and-replace corrupts serialised data; use the plugin.

### 4. Delete the setup files

```bash
rm titanlabs.sql uploads.zip setup.sh wp-cli.phar
```

Leaving a database dump in the document root is a data leak.

### 5. Post-install checklist

- [ ] Change the `admin` password (it is currently the development one)
- [ ] **Settings → Reading** — front page is "Home"
- [ ] **Settings → Permalinks** — re-save to flush rewrite rules
- [ ] **Appearance → Menus** — "Primary Menu" assigned to Primary
- [ ] **WooCommerce → Settings** — real store address, currency EUR
- [ ] Payment methods configured before any real order
- [ ] Legal Notice page — replace the bracketed placeholders
- [ ] Contact/Wholesale/Partner pages — real email addresses
- [ ] SSL certificate issued (Plesk → SSL/TLS Certificates → Let's Encrypt)
- [ ] Search engine visibility **off** while it is a demo
      (Settings → Reading → Discourage search engines)

---

## Required config

`wp-config.php` must contain:

```php
define( 'FS_METHOD', 'direct' );
```

Without it, Elementor cannot write its generated CSS and the front end returns
a 500. `setup.sh` adds this automatically.

### Two settings that silently break the shop

Both are handled by `setup.sh`, but worth knowing if you install by hand:

**WooCommerce "Coming soon" mode.** New WooCommerce installs enable this by
default. The shop, category and product pages then render a placeholder
("Great things are on the horizon") to logged-out visitors, while looking fine
to you as an admin. Turn it off under **WooCommerce → Settings → General →
Site visibility → Live**, or:

```bash
wp option update woocommerce_coming_soon no
wp option update woocommerce_store_pages_only no
```

**A wrong `.htaccess`.** It is environment-specific — `RewriteBase` is
`/titanlabs/` in the local XAMPP subfolder but `/` on the server — so it is
gitignored and `setup.sh` writes the right one per host.

It was committed by mistake until 2026-08-25, and a deployment then overwrote
the server's copy with the XAMPP one. The symptom is distinctive: **the
homepage loads but every other URL returns 500**, because each rewritten
request is sent to a `/titanlabs/` path that does not exist there.

Two checks that separate this from a PHP fault, both runnable from anywhere:

```bash
curl -s -o /dev/null -w "%{http_code}\n" "https://demo2.34devs.com/?post_type=product"  # 200 → PHP is fine
curl -s -o /dev/null -w "%{http_code}\n" "https://demo2.34devs.com/shop/"               # 500 → rewrite is broken
```

Same content, one via query string and one via permalink: if the first works
and the second does not, it is the rewrite layer, not the theme. Fix by
re-saving **Settings → Permalinks** in wp-admin (which rewrites the file), or
re-running `setup.sh`. Missing it entirely gives 404s rather than 500s.

---

## Day-to-day: shipping theme changes

```bash
git add -A
git commit -m "..."
git push origin main
```

Plesk pulls automatically. If a change does not appear:

1. **Plesk → Git** — check the last deployment succeeded
2. Clear any caching plugin
3. **Elementor → Tools → Regenerate CSS** after changing widget markup
4. Hard-refresh the browser (theme CSS is versioned, but proxies cache)

### What the client edits, and what you must not overwrite

The client works in wp-admin: products, prices, images, page content, menus,
and the homepage in Elementor. All of that is in the **database**, which git
never touches. Your pushes cannot overwrite their work.

The one exception:

> **Do not run `wp eval-file content/import-pages.php` on the live site.**
> It rewrites the eleven content pages from the repo and discards whatever the
> client has edited there. It is a first-install tool only.

If you need to change a content page after handover, either make the edit in
wp-admin, or update the file in `content/pages/` and import that single page
deliberately.

---

## Moving from demo to the real domain

```bash
wp search-replace 'https://demo.titanlabs.eu' 'https://titanlabs.eu' \
  --all-tables --precise --skip-columns=guid
wp option update home 'https://titanlabs.eu'
wp option update siteurl 'https://titanlabs.eu'
wp rewrite flush --hard
```

Then re-issue the SSL certificate and turn search engine visibility back on.

---

## Backups

Plesk's own backup covers files and database — schedule it before handover
(**Websites & Domains → Backup & Restore**). Git is version control for the
theme, not a backup of the site: it holds no products, orders or customer
data.
