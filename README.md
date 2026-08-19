# Titan Labs

WordPress + WooCommerce store for Titan Labs — lab-verified research peptides for Europe.

## Stack

- WordPress 7.0
- WooCommerce 10.4
- Elementor (free) — the homepage sections are Elementor widgets
- Custom theme: `wp-content/themes/titan-labs` (no external CSS framework)

Elementor **Pro is not required**. Header and footer stay in the theme, so
everything works on the free version.

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
wp plugin install elementor --activate

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

`wp-config.php` needs `define( 'FS_METHOD', 'direct' );` — without it Elementor
cannot write its generated CSS and the front end returns a 500.

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
├── inc/elementor/
│   ├── loader.php             widget category, registration, editor assets
│   ├── class-titan-widget-base.php   shared controls and section helpers
│   └── widgets/               one class per homepage section
├── template-parts/
│   ├── home/                  hero, categories, bestsellers, spotlight,
│   │                          stack, reviews, quality, coa
│   │                          (fallback when Elementor is inactive)
│   ├── product-card.php       shared product card
│   └── post-card.php
├── page-full-width.php        full-bleed template for Elementor pages
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

## Editing the homepage

The homepage is built in Elementor. **Pages → Home → Edit with Elementor** opens
a canvas where each of the eight sections is a draggable widget under the
**Titan Labs** category in the widget panel:

| Widget | What it does | Key settings |
| --- | --- | --- |
| Hero | Headline block with CTAs and trust stats | Headline, sub-headline, two buttons, repeatable stats, image |
| Category Grid | Product categories with their peptides listed | Which categories and their order, peptides per card, columns |
| Product Tabs | Bestsellers tabbed by format | Repeatable tabs (label + category), products per tab, sort order, columns |
| Product Spotlight | Dark feature block for one product | Featured product or a specific pick, description override, specs |
| Stack Promo | Promotes the volume discount | Copy and button; tiers read live from the Customizer |
| Reviews Summary | Rating breakdown and review cards | Show/hide summary and cards, review count, minimum rating, columns |
| Quality Assurance | Trust stats and feature grid | Repeatable stats and features with icon presets, columns |
| COA Table | Searchable batch results | Search on/off, row limit, anchor id |

Sections are reordered by dragging in the **Structure** panel (the layers icon in
the top bar), and the same widgets can be dropped onto any other page.

New pages that should be full-bleed like the homepage need the
**Full Width (Elementor)** page template — otherwise the page renders inside the
standard container with a page hero.

### If Elementor is removed

`front-page.php` checks whether the homepage was built with Elementor. If the
plugin is deactivated, it falls back to the coded template parts in
`template-parts/home/` and the homepage keeps working unchanged.

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

Site-wide settings that sit outside the Elementor canvas:

- Announcement bar — text, on/off
- Stack builder — discount tiers and percentages (the Stack Promo widget reads these)
- Age verification — on/off, minimum age
- Footer — about text, legal disclaimer

The hero and trust-stat settings also live here, but they only drive the coded
fallback template. While the homepage is built in Elementor, edit those in the
**Hero** widget instead.

## Menus

Five registered locations: `primary`, `footer-shop`, `footer-goal`,
`footer-help`, `footer-legal`. Each footer column falls back to a sensible
default link list when no menu is assigned.

## Note on product imagery

Product images are generated Titan Labs-branded renders. Replace them with real
product photography before launch.
