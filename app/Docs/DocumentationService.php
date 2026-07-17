<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs;

use Bud\DocsApp\Docs\Storage\DocumentationStorage;
use RuntimeException;

final class DocumentationService
{
    public function __construct(
        private readonly DocumentationStorage $storage,
        private readonly MenuParser $menuParser,
    ) {
    }

    /**
     * @return list<string>
     */
    public function listDocumentations(): array
    {
        return $this->storage->listDocumentations();
    }

    public function hasDocumentation(string $documentation): bool
    {
        return $this->storage->documentationExists(PathGuard::documentation($documentation));
    }

    public function menu(string $documentation): Menu
    {
        $documentation = PathGuard::documentation($documentation);

        try {
            return $this->menuParser->parse($this->storage->readMenu($documentation));
        } catch (RuntimeException) {
            return $this->generatedMenu($documentation);
        }
    }

    public function rawMenu(string $documentation): string
    {
        $documentation = PathGuard::documentation($documentation);

        try {
            return $this->storage->readMenu($documentation);
        } catch (RuntimeException) {
            return $this->menuToMarkdown($this->generatedMenu($documentation));
        }
    }

    public function writeMenu(string $documentation, string $content): void
    {
        $this->storage->writeMenu(PathGuard::documentation($documentation), rtrim($content) . "\n");
    }

    public function readPage(string $documentation, string $path): string
    {
        return $this->storage->readPage(PathGuard::documentation($documentation), PathGuard::pagePath($path));
    }

    public function pageExists(string $documentation, string $path): bool
    {
        return $this->storage->pageExists(PathGuard::documentation($documentation), PathGuard::pagePath($path));
    }

    public function writePage(string $documentation, string $path, string $content): void
    {
        $this->storage->writePage(PathGuard::documentation($documentation), PathGuard::pagePath($path), rtrim($content) . "\n");
    }

    public function appendToPage(string $documentation, string $path, string $content): void
    {
        $current = $this->readPage($documentation, $path);
        $this->writePage($documentation, $path, rtrim($current) . "\n\n" . trim($content) . "\n");
    }

    public function readAsset(string $documentation, string $path): string
    {
        return $this->storage->readAsset(PathGuard::documentation($documentation), PathGuard::assetPath($path));
    }

    public function uploadAsset(string $documentation, string $path, string $binaryContent): void
    {
        $this->storage->uploadAsset(PathGuard::documentation($documentation), PathGuard::assetPath($path), $binaryContent);
    }

    /**
     * @return list<string>
     */
    public function listPages(string $documentation): array
    {
        return $this->storage->listMarkdownFiles(PathGuard::documentation($documentation));
    }

    /**
     * @return list<array{title: string, path: string}>
     */
    public function menuPages(string $documentation): array
    {
        return array_map(
            static fn (MenuItem $item): array => ['title' => $item->title, 'path' => (string) $item->path],
            $this->menu($documentation)->flattenPages(),
        );
    }

    /**
     * @return list<MenuItem>
     */
    public function breadcrumb(string $documentation, string $path): array
    {
        $path = PathGuard::pagePath($path);
        $trail = [];

        $walk = static function (array $items, array $parents = []) use (&$walk, &$trail, $path): bool {
            foreach ($items as $item) {
                $nextParents = [...$parents, $item];

                if ($item->path === $path) {
                    $trail = $nextParents;
                    return true;
                }

                if ($walk($item->children, $nextParents)) {
                    return true;
                }
            }

            return false;
        };

        $walk($this->menu($documentation)->items);

        return $trail;
    }

    /**
     * @return array{prev: ?MenuItem, next: ?MenuItem}
     */
    public function previousNext(string $documentation, string $path): array
    {
        $path = PathGuard::pagePath($path);
        $pages = $this->menu($documentation)->flattenPages();

        foreach ($pages as $index => $page) {
            if ($page->path === $path) {
                return [
                    'prev' => $pages[$index - 1] ?? null,
                    'next' => $pages[$index + 1] ?? null,
                ];
            }
        }

        return ['prev' => null, 'next' => null];
    }

    public function urlFor(string $documentation, string $path): string
    {
        $documentation = PathGuard::documentation($documentation);
        $path = PathGuard::pagePath($path);
        $slug = $path === $this->defaultPage($documentation) ? '' : preg_replace('/\.md$/', '', $path);

        return '/' . rawurlencode($documentation) . ($slug ? '/' . implode('/', array_map('rawurlencode', explode('/', $slug))) : '');
    }

    public function pathFromSlug(?string $slug): string
    {
        $slug = trim((string) $slug, '/');

        return $slug === '' ? 'index.md' : PathGuard::pagePath(rawurldecode($slug));
    }

    public function resolvePagePath(string $documentation, ?string $slug): string
    {
        $slug = trim((string) $slug, '/');

        return $slug === '' ? $this->defaultPage($documentation) : PathGuard::pagePath(rawurldecode($slug));
    }

    /**
     * @return list<string>
     */
    public function validate(string $documentation): array
    {
        $errors = [];
        $pages = $this->listPages($documentation);

        foreach ($this->menu($documentation)->flattenPages() as $item) {
            if ($item->path === null) {
                continue;
            }

            if (! in_array($item->path, $pages, true)) {
                $errors[] = sprintf('Menu points to missing page: %s', $item->path);
            }
        }

        if ($errors === [] && ! $this->hasDocumentation($documentation)) {
            throw new RuntimeException('Documentation does not exist.');
        }

        return $errors;
    }

    private function defaultPage(string $documentation): string
    {
        $pages = $this->listPages($documentation);

        foreach (['index.md', 'README.md', 'readme.md'] as $candidate) {
            if (in_array($candidate, $pages, true)) {
                return $candidate;
            }
        }

        return $pages[0] ?? 'index.md';
    }

    private function generatedMenu(string $documentation): Menu
    {
        $pages = $this->listPages($documentation);
        $title = $this->titleFromDocumentationName($documentation);
        $items = [];

        foreach ($pages as $page) {
            $items[] = new MenuItem($this->titleFromPage($documentation, $page), $page);
        }

        return new Menu($title, $items);
    }

    private function titleFromDocumentationName(string $documentation): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $documentation));
    }

    private function titleFromPage(string $documentation, string $path): string
    {
        try {
            $markdown = $this->readPage($documentation, $path);

            if (preg_match('/^#\s+(.+)$/m', $markdown, $match) === 1) {
                return trim($match[1]);
            }
        } catch (RuntimeException) {
        }

        $basename = preg_replace('/\.md$/i', '', basename($path)) ?: $path;

        if (strtolower($basename) === 'readme') {
            return 'Overview';
        }

        return ucwords(str_replace(['-', '_'], ' ', $basename));
    }

    private function menuToMarkdown(Menu $menu): string
    {
        $lines = ['# ' . $menu->title, ''];

        $writeItems = static function (array $items, int $level = 0) use (&$writeItems, &$lines): void {
            foreach ($items as $item) {
                $indent = str_repeat('  ', $level);
                $lines[] = $item->path === null
                    ? $indent . '- ' . $item->title
                    : sprintf('%s- [%s](%s)', $indent, $item->title, $item->path);

                if ($item->children !== []) {
                    $writeItems($item->children, $level + 1);
                }
            }
        };

        $writeItems($menu->items);

        return rtrim(implode("\n", $lines)) . "\n";
    }
}
