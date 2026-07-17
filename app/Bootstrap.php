<?php

declare(strict_types=1);

namespace Bud\DocsApp;

use Bud\DocsApp\Docs\DocumentationService;
use Bud\DocsApp\Docs\MarkdownRenderer;
use Bud\DocsApp\Docs\MenuParser;
use Bud\DocsApp\Docs\SearchService;
use Bud\DocsApp\Docs\Storage\GitHubStorage;
use Bud\DocsApp\Docs\Storage\LocalFilesystemStorage;
use Bud\DocsApp\Docs\Storage\DocumentationStorage;

final class Bootstrap
{
    /**
     * @return array{
     *     config: array<string, mixed>,
     *     storage: DocumentationStorage,
     *     docs: DocumentationService,
     *     renderer: MarkdownRenderer,
     *     search: SearchService
     * }
     */
    public static function services(): array
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        $storage = self::storage($config);
        $menuParser = new MenuParser();
        $docs = new DocumentationService($storage, $menuParser);
        $renderer = new MarkdownRenderer();
        $search = new SearchService($docs, $renderer, (string) $config['cache_path']);

        return [
            'config' => $config,
            'storage' => $storage,
            'docs' => $docs,
            'renderer' => $renderer,
            'search' => $search,
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function storage(array $config): DocumentationStorage
    {
        if (($config['storage'] ?? 'local') === 'github') {
            /** @var array<string, string> $github */
            $github = $config['github'];

            return new GitHubStorage($github);
        }

        /** @var array<string, string> $local */
        $local = $config['local'];

        return new LocalFilesystemStorage($local['docs_path']);
    }
}
