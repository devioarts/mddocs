<?php

declare(strict_types=1);

return [
    'storage' => getenv('DOCS_STORAGE') ?: 'local',

    'local' => [
        'docs_path' => dirname(__DIR__) . '/docs',
    ],

    'github' => [
        'owner' => getenv('GITHUB_OWNER') ?: '',
        'repo' => getenv('GITHUB_REPO') ?: '',
        'branch' => getenv('GITHUB_BRANCH') ?: 'main',
        'docs_path' => trim(getenv('GITHUB_DOCS_PATH') ?: 'docs', '/'),
        'token' => getenv('GITHUB_TOKEN') ?: '',
        'committer_name' => getenv('GITHUB_COMMITTER_NAME') ?: 'Docs MCP',
        'committer_email' => getenv('GITHUB_COMMITTER_EMAIL') ?: 'docs-mcp@example.com',
    ],

    'cache_path' => dirname(__DIR__) . '/var/cache',
    'backup_path' => dirname(__DIR__) . '/var/backups',
    'audit_log' => dirname(__DIR__) . '/var/log/mcp-audit.log',

    'debug' => getenv('APP_DEBUG') === '1',
    'error_log' => dirname(__DIR__) . '/var/log/app-error.log',
];
