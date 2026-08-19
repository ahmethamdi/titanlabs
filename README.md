# Titan Labs

WordPress + WooCommerce store for Titan Labs — lab-verified research peptides for Europe.

## Stack

- WordPress 7.0
- WooCommerce 10.4
- Custom theme: `wp-content/themes/titan-labs` (no page builder, no external CSS framework)

## Local setup (XAMPP)

```bash
# 1. Database
mysql -u root -e "CREATE DATABASE titanlabs DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. WordPress core + WooCommerce (not tracked in git)
wp core download --locale=en_GB
wp config create --dbname=titanlabs --dbuser=root --dbpass='' --dbhost=127.0.0.1
wp core install --url="http://localhost/titanlabs" --title="Titan Labs" \
  --admin_user=admin --admin_password='...' --admin_email=you@example.com

wp plugin install woocommerce --activate

# 3. Theme
wp theme activate titan-labs
```

### Store settings

| Setting | Value |
| --- | --- |
| Currency | EUR, symbol right with space |
| Decimal / thousand separator | `,` / `.` |
| Base country | Germany |
| Permalinks | `/%postname%/` |

## Theme

### Structure

```
titan-labs/
├── functions.php              theme setup, WooCommerce hooks, stack discount, lab meta
├── front-page.php             homepage — composes the 8 sections below
├── header.php / footer.php    header, nav, age gate / footer columns
├── inc/
│   ├── customizer.php         all editable copy and settings
│   └── nav-walker.php         menu rendering + fallbacks
├── template-parts/
│   ├── home/                  hero, categories, bestsellers, spotlight,
│   │                          stack, reviews, quality, coa
│   ├── product-card.php       shared product card
│   └── post-card.php
├── woocommerce/               archive-product.php, content-product.php
└── assets/css/app.css         design tokens + all styling
```

### Design tokens

Defined at the top of `assets/css/app.css`. Light mode on `:root`, dark mode on
`:root.dark`. The brand palette is the Titan Labs chrome blue:

| Token | Light | Purpose |
| --- | --- | --- |
| `--color-accent` | `#1663d8` | primary brand blue |
| `--color-accent-deep` | `#0d47a1` | hover / pressed |
| `--color-canvas` | `#f6f8fc` | page background |
| `--color-ink-strong` | `#0b1220` | headings |

Typography: Montserrat (display) + Inter (body), loaded from Google Fonts.

Dark mode is chosen by `prefers-color-scheme` on first visit and then persisted
to `localStorage` under `titanTheme`; the boot script in `functions.php` applies
the class before paint so there is no flash.

## Features

### Stack discount

An automatic cart-level volume discount, applied as a negative fee in
`titan_apply_stack_discount()`:

- 4+ items → −5%
- 10+ items → −10%

Both tiers (quantity and percentage) are editable in
**Customizer → Titan Labs Settings → Stack Builder Discount**.

### Lab data / Certificate of Analysis

Each product has a **Titan Labs — Lab Data** meta box holding batch number,
tested purity, net content, heavy metals / endotoxins / sterility results and
COA links. `titan_get_coa_rows()` aggregates these into the searchable batch
table on the homepage.

### Age verification

A 21+ gate shown before first interaction, dismissed into `localStorage`.
Toggle and minimum age live in **Customizer → Titan Labs Settings → Age Verification**.

## Customizer settings

All homepage copy is editable without touching code:

- Announcement bar — text, on/off
- Homepage hero — eyebrow, headline, sub-headline, CTA, image
- Trust stats — three value/label pairs
- Stack builder — tiers and percentages
- Age verification — on/off, minimum age
- Footer — about text, legal disclaimer

## Menus

Five registered locations: `primary`, `footer-shop`, `footer-goal`,
`footer-help`, `footer-legal`. Each footer column falls back to a sensible
default link list when no menu is assigned.

## Note on product imagery

Product images are generated Titan Labs-branded renders. Replace them with real
product photography before launch.
