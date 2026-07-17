<?php

declare(strict_types=1);

use Bud\DocsApp\Mcp\ServerFactory;
use GuzzleHttp\Psr7\ServerRequest;
use Mcp\Server\Transport\StreamableHttpTransport;

require dirname(__DIR__) . '/vendor/autoload.php';

$token = getenv('MCP_BEARER_TOKEN') ?: '';

if ($token !== '') {
    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (! hash_equals('Bearer ' . $token, $authorization)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
}

$request = ServerRequest::fromGlobals();
$transport = new StreamableHttpTransport($request);
$response = ServerFactory::build(dirname(__DIR__) . '/var/mcp-sessions')->run($transport);

http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header($name . ': ' . $value, false);
    }
}

echo (string) $response->getBody();
