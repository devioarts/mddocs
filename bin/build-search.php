#!/usr/bin/env php
<?php

declare(strict_types=1);

use Bud\DocsApp\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

$services = Bootstrap::services();
$documentations = array_slice($argv, 1) ?: $services['docs']->listDocumentations();

foreach ($documentations as $documentation) {
    $services['search']->rebuild($documentation);
    fwrite(STDOUT, sprintf("Built search index for %s\n", $documentation));
}
