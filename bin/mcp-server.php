#!/usr/bin/env php
<?php

declare(strict_types=1);

use Bud\DocsApp\Mcp\ServerFactory;
use Mcp\Server\Transport\StdioTransport;

require dirname(__DIR__) . '/vendor/autoload.php';

$server = ServerFactory::build();
$status = $server->run(new StdioTransport());

exit($status);
