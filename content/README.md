# Content pages

Source HTML for the designed content pages. Each file is the body of one page,
written with the theme's component classes.

To (re)load them into WordPress:

```bash
wp eval-file content/import-pages.php
```

The importer wraps each file in a Gutenberg custom-HTML block, sets the page
title, and assigns the **Full Width (Elementor)** template. It updates pages by
slug, so the pages must already exist.

| File | Page slug |
| --- | --- |
| `about.html` | `about-us` |
| `faq.html` | `faq` |
| `contact.html` | `contact-us` |
| `shipping.html` | `shipping-policy` |
| `refund.html` | `refund-policy` |
| `rou.html` | `research-use-only-policy` |
| `privacy.html` | `privacy-policy` |
| `terms.html` | `terms-of-service` |
| `legal.html` | `legal-notice` |
| `wholesale.html` | `wholesale` |
| `affiliate.html` | `affiliate-program` |

Editing a page in wp-admin does **not** write back to these files. If you make
changes in WordPress that should be kept, update the file here too — otherwise
the next import will overwrite them.
