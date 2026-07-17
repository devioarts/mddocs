---
title: Deployment
---

# Deployment

MDDocs is a traditional PHP application. It does not require a Node.js build, a database, or a worker process.

## Production checklist

- run `composer install --no-dev --optimize-autoloader`
- point the web server document root to `public/`
- block public access to `app/`, `docs/`, `config/`, `var/`, and `vendor/`
- make `var/` writable by the PHP process
- set `MCP_BEARER_TOKEN` if the HTTP MCP endpoint is available
- decide whether to use `local` or `github` storage
- back up documentation regularly or version `docs/` in Git

## Apache

`public/.htaccess` contains rewrite rules for clean URLs. Set the document root to:

```text
public/
```

## HestiaCP Nginx

For HestiaCP servers running standalone Nginx + PHP-FPM, MDDocs includes ready-to-copy templates:

```text
deploy/hestiacp/nginx/php-fpm/mddocs.tpl
deploy/hestiacp/nginx/php-fpm/mddocs.stpl
```

Set the Hestia custom document root to the MDDocs `public/` directory:

```text
/home/<user>/web/<domain>/public_html/public
```

The project itself should live directly in:

```text
/home/<user>/web/<domain>/public_html/
```

Install MDDocs with one of these methods.

### Direct install from GitHub

```bash
cd /home/<user>/web/<domain>/public_html
git clone https://github.com/devioarts/mddocs.git .
composer install --no-dev --optimize-autoloader
composer build-search
```

### Local upload

Prepare the project locally:

```bash
composer install --no-dev --optimize-autoloader
composer build-search
```

Upload the project contents to:

```text
/home/<user>/web/<domain>/public_html/
```

Include `vendor/` when Composer will not be run on the server. Do not upload `.git/`, `.idea/`, `var/server.log`, or `var/server.pid`.

After upload:

```bash
cd /home/<user>/web/<domain>/public_html
chown -R <user>:<user> var
chmod -R ug+rw var
```

Install the templates on Ubuntu/HestiaCP:

```bash
cp deploy/hestiacp/nginx/php-fpm/mddocs.tpl /usr/local/hestia/data/templates/web/nginx/php-fpm/mddocs.tpl
cp deploy/hestiacp/nginx/php-fpm/mddocs.stpl /usr/local/hestia/data/templates/web/nginx/php-fpm/mddocs.stpl
```

Then set the custom document root in HestiaCP, select the `mddocs` Nginx template for the domain, or use:

```bash
v-change-web-domain-tpl <user> <domain> mddocs
v-rebuild-web-domain <user> <domain>
```

See `deploy/hestiacp/README.md` in the repository for the complete HestiaCP guide.

## Nginx

The basic principle is to route missing files to `index.php`:

```nginx
location / {
    try_files $uri /index.php$is_args$args;
}
```

PHP-FPM configuration depends on the host, but the document root should remain `public/`.

## Shared hosting

If the host cannot set the document root to `public/`, prefer a host that can. Exposing the whole project root publicly may reveal configuration, runtime files, or vendor packages.

## Cache and rebuilds

Search indexes are stored in `var/cache/`. After larger content changes, run:

```bash
composer build-search
```
