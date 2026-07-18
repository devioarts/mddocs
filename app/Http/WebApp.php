<?php

declare(strict_types=1);

namespace Bud\DocsApp\Http;

use Bud\DocsApp\Docs\DocumentationService;
use Bud\DocsApp\Docs\MarkdownRenderer;
use Bud\DocsApp\Docs\MenuItem;
use Bud\DocsApp\Docs\SearchService;
use Throwable;

final class WebApp
{
    public function __construct(
        private readonly DocumentationService $docs,
        private readonly MarkdownRenderer $renderer,
        private readonly SearchService $search,
        private readonly bool $debug = false,
        private readonly ?string $errorLog = null,
    ) {
    }

    public function handle(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        try {
            if ($path === '/search') {
                $this->search($uri);
                return;
            }

            if (str_starts_with($path, '/_asset/')) {
                $this->asset($path);
                return;
            }

            if ($method !== 'GET') {
                $this->error(405, 'Method not allowed');
                return;
            }

            $segments = array_values(array_filter(explode('/', trim($path, '/')), static fn (string $segment): bool => $segment !== ''));

            if ($segments === []) {
                $this->home();
                return;
            }

            $documentation = rawurldecode((string) array_shift($segments));
            $slug = implode('/', $segments);
            $this->page($documentation, $slug);
        } catch (Throwable $exception) {
            $this->logError($exception);
            $this->error(500, $this->debug ? $exception->getMessage() : 'Something went wrong. Check the server log for details.');
        }
    }

    private function logError(Throwable $exception): void
    {
        if ($this->errorLog === null) {
            return;
        }

        $entry = [
            'time' => date(DATE_ATOM),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        @mkdir(dirname($this->errorLog), 0777, true);
        file_put_contents($this->errorLog, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    }

    private function home(): void
    {
        $documentations = $this->docs->listDocumentations();

        if (count($documentations) === 1) {
            header('Location: /' . rawurlencode($documentations[0]), true, 302);
            return;
        }

        $body = '<main class="home"><h1>Documentation</h1>';

        if ($documentations === []) {
            $body .= '<p>No documentation exists yet. Add a directory under <code>docs/</code> with Markdown files. Use <code>.menu.md</code> when you want explicit navigation.</p>';
        } else {
            $body .= '<ul class="doc-list">';

            foreach ($documentations as $documentation) {
                $menu = $this->docs->menu($documentation);
                $body .= sprintf(
                    '<li><a href="/%s">%s</a></li>',
                    $this->e($documentation),
                    $this->e($menu->title),
                );
            }

            $body .= '</ul>';
        }

        $body .= '</main>';
        $this->layout('Documentation', null, $body);
    }

    private function page(string $documentation, string $slug): void
    {
        if (! $this->docs->hasDocumentation($documentation)) {
            $this->error(404, 'Documentation was not found.');
            return;
        }

        $path = $this->docs->resolvePagePath($documentation, $slug);
        $markdown = $this->docs->readPage($documentation, $path);
        $menu = $this->docs->menu($documentation);
        $title = $this->renderer->title($markdown, basename($path, '.md'));
        $html = $this->rewriteLinks($this->renderer->render($markdown), $documentation, $path);
        $outline = $this->renderer->outline($markdown);
        $breadcrumb = $this->docs->breadcrumb($documentation, $path);
        $previousNext = $this->docs->previousNext($documentation, $path);
        $lastModified = $this->docs->pageLastModified($documentation, $path);

        $body = '<div class="app-shell" data-doc="' . $this->e($documentation) . '">';
        $body .= '<aside class="sidebar" id="sidebar"><div class="sidebar-inner"><div class="sidebar-title">' . $this->e($menu->title) . '</div>';
        $body .= $this->navigation($documentation, $menu->items, $path);
        $body .= '</div></aside>';
        $body .= '<main class="content">';
        $body .= $this->breadcrumb($documentation, $breadcrumb);
        $body .= '<article class="markdown-body">' . $html . '</article>';
        $body .= $this->lastModified($lastModified);
        $body .= $this->previousNext($documentation, $previousNext['prev'], $previousNext['next']);
        $body .= '</main>';
        $body .= '<aside class="toc"><div class="toc-inner"><div class="toc-title">On this page</div>' . $this->outline($outline) . '</div></aside>';
        $body .= '</div>';

        $this->layout($title . ' - ' . $menu->title, $documentation, $body);
    }

    private function search(string $uri): void
    {
        $query = [];
        parse_str((string) parse_url($uri, PHP_URL_QUERY), $query);
        $documentation = isset($query['doc']) ? (string) $query['doc'] : ($this->docs->listDocumentations()[0] ?? '');
        $term = isset($query['q']) ? (string) $query['q'] : '';

        header('Content-Type: application/json; charset=utf-8');

        if ($documentation === '') {
            echo json_encode([]);
            return;
        }

        echo json_encode($this->search->search($documentation, $term), JSON_UNESCAPED_UNICODE);
    }

    private function asset(string $path): void
    {
        $rest = substr($path, strlen('/_asset/'));
        [$documentation, $assetPath] = array_pad(explode('/', $rest, 2), 2, '');
        $binary = $this->docs->readAsset(rawurldecode($documentation), rawurldecode($assetPath));
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary) ?: 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=3600');
        echo $binary;
    }

    private function layout(string $title, ?string $documentation, string $body): void
    {
        $docAttribute = $documentation !== null ? ' data-current-doc="' . $this->e($documentation) . '"' : '';
        $themeAttribute = ($_COOKIE['docs-theme'] ?? '') === 'dark' ? ' data-theme="dark"' : '';
        $layoutAttribute = ($_COOKIE['docs-layout'] ?? '') === 'full' ? ' data-layout="full"' : '';

        echo '<!doctype html><html lang="en"' . $themeAttribute . $layoutAttribute . '><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . $this->e($title) . '</title>';
        echo '<link rel="stylesheet" href="/assets/vendor/prism/prism-tomorrow.min.css">';
        echo '<link rel="stylesheet" href="/assets/vendor/prism/prism-toolbar.min.css">';
        echo '<link rel="stylesheet" href="/assets/app.css">';
        echo '</head><body' . $docAttribute . '>';
        echo '<header class="topbar">';
        echo '<button class="icon-button menu-toggle" type="button" aria-label="Menu" data-menu-toggle><span></span><span></span><span></span></button>';
        echo '<a class="brand" href="/">Docs</a>';
        echo '<button class="search-trigger" type="button" data-search-open><span>Search documentation</span><kbd>/</kbd></button>';
        echo '<button class="icon-button layout-toggle" type="button" aria-label="Toggle full-width layout" data-layout-toggle></button>';
        echo '<button class="theme-toggle" type="button" aria-label="Toggle theme" data-theme-toggle></button>';
        echo '</header>';
        echo $body;
        echo '<div class="search-dialog" data-search-dialog hidden>';
        echo '<div class="search-panel"><input type="search" placeholder="Search..." data-search-input autocomplete="off"><div class="search-results" data-search-results></div></div>';
        echo '</div>';
        echo '<script>window.Prism = window.Prism || {}; window.Prism.manual = true;</script>';
        echo '<script src="/assets/vendor/prism/prism-core.min.js"></script>';
        echo '<script src="/assets/vendor/prism/prism-toolbar.min.js"></script>';
        echo '<script src="/assets/vendor/prism/prism-copy-to-clipboard.min.js"></script>';
        echo '<script src="/assets/vendor/prism/prism-autoloader.min.js"></script>';
        echo '<script>Prism.plugins.autoloader.languages_path = "/assets/vendor/prism/"; Prism.highlightAll();</script>';
        echo '<script src="/assets/app.js"></script>';
        echo '</body></html>';
    }

    /**
     * @param list<MenuItem> $items
     */
    private function navigation(string $documentation, array $items, string $currentPath): string
    {
        $html = '<nav class="nav-tree">';

        foreach ($items as $item) {
            $active = $item->path === $currentPath ? ' is-active' : '';
            $html .= '<div class="nav-item' . $active . '">';

            if ($item->path !== null) {
                $html .= sprintf('<a href="%s">%s</a>', $this->docs->urlFor($documentation, $item->path), $this->e($item->title));
            } else {
                $html .= '<span>' . $this->e($item->title) . '</span>';
            }

            if ($item->children !== []) {
                $html .= $this->navigation($documentation, $item->children, $currentPath);
            }

            $html .= '</div>';
        }

        return $html . '</nav>';
    }

    /**
     * @param list<MenuItem> $items
     */
    private function breadcrumb(string $documentation, array $items): string
    {
        if ($items === []) {
            return '';
        }

        $html = '<nav class="breadcrumb">';

        foreach ($items as $index => $item) {
            if ($item->path !== null && $index < count($items) - 1) {
                $html .= '<a href="' . $this->docs->urlFor($documentation, $item->path) . '">' . $this->e($item->title) . '</a>';
            } else {
                $html .= '<span>' . $this->e($item->title) . '</span>';
            }
        }

        return $html . '</nav>';
    }

    /**
     * @param list<array{level: int, title: string, id: string}> $outline
     */
    private function outline(array $outline): string
    {
        if ($outline === []) {
            return '<p class="muted">No headings</p>';
        }

        $html = '<nav class="toc-links">';

        foreach ($outline as $item) {
            $html .= sprintf(
                '<a class="toc-level-%d" href="#%s">%s</a>',
                $item['level'],
                $this->e($item['id']),
                $this->e($item['title']),
            );
        }

        return $html . '</nav>';
    }

    private function lastModified(?int $timestamp): string
    {
        if ($timestamp === null) {
            return '';
        }

        return '<p class="last-modified muted">Last updated on ' . $this->e(date('F j, Y', $timestamp)) . '</p>';
    }

    private function previousNext(string $documentation, ?MenuItem $previous, ?MenuItem $next): string
    {
        if ($previous === null && $next === null) {
            return '';
        }

        $html = '<nav class="prev-next">';
        $html .= $previous !== null
            ? '<a class="prev" href="' . $this->docs->urlFor($documentation, (string) $previous->path) . '"><span>Previous</span>' . $this->e($previous->title) . '</a>'
            : '<span></span>';
        $html .= $next !== null
            ? '<a class="next" href="' . $this->docs->urlFor($documentation, (string) $next->path) . '"><span>Next</span>' . $this->e($next->title) . '</a>'
            : '<span></span>';

        return $html . '</nav>';
    }

    private function rewriteLinks(string $html, string $documentation, string $currentPath): string
    {
        $html = preg_replace_callback('/href="([^"]+)"/', function (array $match) use ($documentation, $currentPath): string {
            $href = html_entity_decode($match[1]);

            if ($this->isExternalOrAnchor($href) || ! str_contains($href, '.md')) {
                return $match[0];
            }

            [$target, $fragment] = array_pad(explode('#', $href, 2), 2, '');
            $resolved = $this->resolveRelativePath($currentPath, $target);

            return 'href="' . $this->e($this->docs->urlFor($documentation, $resolved) . ($fragment !== '' ? '#' . $fragment : '')) . '"';
        }, $html) ?? $html;

        return preg_replace_callback('/src="([^"]+)"/', function (array $match) use ($documentation, $currentPath): string {
            $src = html_entity_decode($match[1]);

            if ($this->isExternalOrAnchor($src)) {
                return $match[0];
            }

            $resolved = $this->resolveRelativePath($currentPath, $src);

            if (! str_starts_with($resolved, 'assets/')) {
                return $match[0];
            }

            return 'src="/_asset/' . rawurlencode($documentation) . '/' . implode('/', array_map('rawurlencode', explode('/', $resolved))) . '"';
        }, $html) ?? $html;
    }

    private function resolveRelativePath(string $currentPath, string $target): string
    {
        $base = dirname($currentPath);
        $combined = ($base === '.' ? '' : $base . '/') . $target;
        $parts = [];

        foreach (explode('/', $combined) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($parts);
                continue;
            }

            $parts[] = $part;
        }

        return implode('/', $parts);
    }

    private function isExternalOrAnchor(string $value): bool
    {
        return str_starts_with($value, '#') || str_starts_with($value, '/') || preg_match('/^[a-z][a-z0-9+.-]*:/i', $value) === 1;
    }

    private function error(int $status, string $message): void
    {
        http_response_code($status);
        $this->layout((string) $status, null, '<main class="home"><h1>' . $status . '</h1><p>' . $this->e($message) . '</p></main>');
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
