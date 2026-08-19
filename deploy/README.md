# Deployment bundle

`setup.sh` provisions a fresh Plesk WordPress install with this site.

It expects two files that are **not** in git, because they hold site data
rather than code:

| File | What it is | How to regenerate |
| --- | --- | --- |
| `titanlabs.sql` | Database dump: products, pages, menus, settings | `wp db export titanlabs.sql --add-drop-table` |
| `uploads.zip` | `wp-content/uploads` — product images | `cd wp-content && zip -r uploads.zip uploads` |

Put all three in the WordPress root on the server and run:

```bash
bash setup.sh https://demo.titanlabs.eu
```

Delete the dump, the zip and the script afterwards — a database dump sitting
in the document root is a data leak.

Full instructions, including the Plesk Git setup, are in [`../DEPLOY.md`](../DEPLOY.md).
