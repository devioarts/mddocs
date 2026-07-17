<?php

declare(strict_types=1);

namespace Bud\DocsApp\Mcp;

use Bud\DocsApp\Docs\DocumentationService;
use Bud\DocsApp\Docs\MarkdownRenderer;
use Bud\DocsApp\Docs\PathGuard;
use Bud\DocsApp\Docs\SearchService;
use Mcp\Exception\ToolCallException;
use Symfony\Component\Filesystem\Filesystem;

final class DocumentationTools
{
    private Filesystem $filesystem;

    public function __construct(
        private readonly DocumentationService $docs,
        private readonly MarkdownRenderer $renderer,
        private readonly SearchService $search,
        private readonly string $backupPath,
        private readonly string $auditLog,
    ) {
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir([$this->backupPath, dirname($this->auditLog)]);
    }

    /**
     * Lists all documentation roots.
     *
     * @return list<string>
     */
    public function listDocumentations(): array
    {
        return $this->docs->listDocumentations();
    }

    /**
     * Lists Markdown pages in a documentation root.
     *
     * @return list<string>
     */
    public function listPages(string $documentation): array
    {
        return $this->docs->listPages($documentation);
    }

    /**
     * Reads a Markdown page.
     *
     * @return array{documentation: string, path: string, title: string, content: string, outline: list<array{level: int, title: string, id: string}>}
     */
    public function getPage(string $documentation, string $path): array
    {
        $content = $this->docs->readPage($documentation, $path);

        return [
            'documentation' => PathGuard::documentation($documentation),
            'path' => PathGuard::pagePath($path),
            'title' => $this->renderer->title($content, basename($path, '.md')),
            'content' => $content,
            'outline' => $this->renderer->outline($content),
        ];
    }

    /**
     * Searches documentation content.
     *
     * @return list<array{title: string, path: string, url: string, breadcrumb: string, excerpt: string, score: int}>
     */
    public function searchDocs(string $documentation, string $query): array
    {
        return $this->search->search($documentation, $query);
    }

    /**
     * Creates a new Markdown page. Update menu.md separately if it should appear in navigation.
     *
     * @return array{ok: bool, path: string}
     */
    public function createPage(string $documentation, string $path, string $content): array
    {
        $path = PathGuard::pagePath($path);

        if ($this->docs->pageExists($documentation, $path)) {
            throw new ToolCallException(sprintf('Page already exists: %s', $path));
        }

        $this->docs->writePage($documentation, $path, $content);
        $this->search->rebuild($documentation);
        $this->audit('create_page', $documentation, $path);

        return ['ok' => true, 'path' => $path];
    }

    /**
     * Replaces a Markdown page.
     *
     * @return array{ok: bool, path: string}
     */
    public function updatePage(string $documentation, string $path, string $content): array
    {
        $path = PathGuard::pagePath($path);
        $this->backup($documentation, $path, $this->docs->readPage($documentation, $path));
        $this->docs->writePage($documentation, $path, $content);
        $this->search->rebuild($documentation);
        $this->audit('update_page', $documentation, $path);

        return ['ok' => true, 'path' => $path];
    }

    /**
     * Appends Markdown content to an existing page.
     *
     * @return array{ok: bool, path: string}
     */
    public function appendToPage(string $documentation, string $path, string $content): array
    {
        $path = PathGuard::pagePath($path);
        $this->backup($documentation, $path, $this->docs->readPage($documentation, $path));
        $this->docs->appendToPage($documentation, $path, $content);
        $this->search->rebuild($documentation);
        $this->audit('append_to_page', $documentation, $path);

        return ['ok' => true, 'path' => $path];
    }

    /**
     * Reads menu.md.
     */
    public function getMenu(string $documentation): string
    {
        return $this->docs->rawMenu($documentation);
    }

    /**
     * Replaces menu.md.
     *
     * @return array{ok: bool}
     */
    public function updateMenu(string $documentation, string $content): array
    {
        $this->backup($documentation, 'menu.md', $this->docs->rawMenu($documentation));
        $this->docs->writeMenu($documentation, $content);
        $this->search->rebuild($documentation);
        $this->audit('update_menu', $documentation, 'menu.md');

        return ['ok' => true];
    }

    /**
     * Uploads a base64 encoded asset below assets/.
     *
     * @return array{ok: bool, path: string}
     */
    public function uploadAsset(string $documentation, string $path, string $base64Content): array
    {
        $path = PathGuard::assetPath($path);
        $binary = base64_decode($base64Content, true);

        if ($binary === false) {
            throw new ToolCallException('Asset content is not valid base64.');
        }

        $this->docs->uploadAsset($documentation, $path, $binary);
        $this->audit('upload_asset', $documentation, $path);

        return ['ok' => true, 'path' => $path];
    }

    /**
     * Validates menu links against existing Markdown pages.
     *
     * @return list<string>
     */
    public function validateDocumentation(string $documentation): array
    {
        return $this->docs->validate($documentation);
    }

    /**
     * Rebuilds the JSON search index.
     *
     * @return array{ok: bool}
     */
    public function rebuildSearchIndex(string $documentation): array
    {
        $this->search->rebuild($documentation);
        $this->audit('rebuild_search_index', $documentation, null);

        return ['ok' => true];
    }

    private function backup(string $documentation, string $path, string $content): void
    {
        $safeDoc = preg_replace('/[^A-Za-z0-9._-]/', '_', $documentation) ?: 'docs';
        $safePath = str_replace('/', '__', $path);
        $target = rtrim($this->backupPath, '/') . '/' . $safeDoc . '/' . date('Ymd-His') . '-' . $safePath;
        $this->filesystem->mkdir(dirname($target));
        file_put_contents($target, $content);
    }

    private function audit(string $action, string $documentation, ?string $path): void
    {
        $entry = [
            'time' => date(DATE_ATOM),
            'action' => $action,
            'documentation' => $documentation,
            'path' => $path,
        ];

        file_put_contents($this->auditLog, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    }
}
