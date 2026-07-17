<?php

declare(strict_types=1);

use Bud\DocsApp\Bootstrap;
use Bud\DocsApp\Http\WebApp;

require dirname(__DIR__) . '/vendor/autoload.php';

if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $file = __DIR__ . $path;

    if (is_file($file)) {
        return false;
    }
}

$services = Bootstrap::services();
$app = new WebApp(
    $services['docs'],
    $services['renderer'],
    $services['search'],
    (bool) $services['config']['debug'],
    (string) $services['config']['error_log'],
);
$app->handle($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
