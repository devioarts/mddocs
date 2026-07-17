# HestiaCP Deployment

This directory contains HestiaCP templates for running MDDocs on a standalone Nginx + PHP-FPM server.

## Required Hestia setting

Set the Hestia custom document root to the MDDocs `public/` directory:

```text
/home/<user>/web/<domain>/public_html/public
```

The MDDocs project itself should live directly in:

```text
/home/<user>/web/<domain>/public_html/
```

This keeps `app/`, `config/`, `docs/`, `skills/`, `vendor/`, and `var/` outside the public web root.

## Install MDDocs files

Use one of these installation methods.

### Option A: direct install from GitHub

For a fresh deployment, clone MDDocs directly into the Hestia domain directory:

```bash
cd /home/<user>/web/<domain>/public_html
git clone https://github.com/devioarts/mddocs.git .
composer install --no-dev --optimize-autoloader
composer build-search
```

Make runtime directories writable by the domain user:

```bash
chown -R <user>:<user> var
chmod -R ug+rw var
```

### Option B: local upload

Use this when the server cannot access GitHub, Composer is unavailable on the server, or you want to upload a prepared build.

From your local machine:

```bash
composer install --no-dev --optimize-autoloader
composer build-search
```

Upload the project contents to:

```text
/home/<user>/web/<domain>/public_html/
```

Upload these paths:

```text
app/
bin/
config/
docs/
public/
skills/
vendor/
var/
composer.json
composer.lock
CONTRIBUTING.md
README.md
```

Do not upload:

```text
.git/
.idea/
var/server.log
var/server.pid
```

After upload, on the server:

```bash
cd /home/<user>/web/<domain>/public_html
chown -R <user>:<user> var
chmod -R ug+rw var
```

## Install the Hestia template on Ubuntu

Run as `root`:

```bash
cp deploy/hestiacp/nginx/php-fpm/mddocs.tpl /usr/local/hestia/data/templates/web/nginx/php-fpm/mddocs.tpl
cp deploy/hestiacp/nginx/php-fpm/mddocs.stpl /usr/local/hestia/data/templates/web/nginx/php-fpm/mddocs.stpl
```

If you are copying from a local machine, upload the two files first, then copy them into the Hestia template directory on the server.

## Enable the template

In HestiaCP:

1. Open the web domain.
2. Set custom document root to `/home/<user>/web/<domain>/public_html/public`.
3. Set the Nginx template to `mddocs`.
4. Save the domain.
5. Rebuild the user configuration if needed.

CLI alternative:

```bash
v-change-web-domain-tpl <user> <domain> mddocs
v-rebuild-web-domain <user> <domain>
```

If your Hestia version does not expose `v-rebuild-web-domain`, rebuild the whole user:

```bash
v-rebuild-user <user> yes
```

## Test Nginx

```bash
nginx -t
systemctl reload nginx
```

Then open:

```text
https://<domain>/
```

If there is only one documentation set, MDDocs redirects `/` to that documentation automatically.
