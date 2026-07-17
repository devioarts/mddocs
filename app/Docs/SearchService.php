<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs;

use Symfony\Component\Filesystem\Filesystem;

final class SearchService
{
    private Filesystem $filesystem;

    public function __construct(
        private readonly DocumentationService $docs,
        private readonly MarkdownRenderer $renderer,
        private readonly string $cachePath,
    ) {
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->cachePath);
    }

    /**
     * @return list<array{title: string, path: string, url: string, breadcrumb: string, excerpt: string, score: int}>
     */
    public function search(string $documentation, string $query): array
    {
        $query = mb_strtolower(trim($query));

        if ($query === '') {
            return [];
        }

        $results = [];

        foreach ($this->index($documentation) as $entry) {
            $haystack = mb_strtolower($entry['title'] . ' ' . $entry['breadcrumb'] . ' ' . $entry['text']);
            $position = mb_strpos($haystack, $query);

            if ($position === false) {
                continue;
            }

            $score = 10;

            if (mb_strpos(mb_strtolower($entry['title']), $query) !== false) {
                $score += 30;
            }

            $results[] = [
                'title' => $entry['title'],
                'path' => $entry['path'],
                'url' => $entry['url'],
                'breadcrumb' => $entry['breadcrumb'],
                'excerpt' => $this->excerpt($entry['text'], $query),
                'score' => $score,
            ];
        }

        usort($results, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_slice($results, 0, 20);
    }

    public function rebuild(string $documentation): void
    {
        $index = $this->buildIndex($documentation);
        file_put_contents($this->cacheFile($documentation), json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return list<array{title: string, path: string, url: string, breadcrumb: string, text: string}>
     */
    private function index(string $documentation): array
    {
        $file = $this->cacheFile($documentation);

        if (! is_file($file)) {
            $this->rebuild($documentation);
        }

        $data = json_decode((string) file_get_contents($file), true);

        return is_array($data) ? $data : [];
    }

    /**
     * @return list<array{title: string, path: string, url: string, breadcrumb: string, text: string}>
     */
    private function buildIndex(string $documentation): array
    {
        $index = [];
        $menuTitles = [];

        foreach ($this->docs->menuPages($documentation) as $page) {
            $menuTitles[$page['path']] = $page['title'];
        }

        foreach ($this->docs->listPages($documentation) as $path) {
            $markdown = $this->docs->readPage($documentation, $path);
            $breadcrumb = array_map(static fn (MenuItem $item): string => $item->title, $this->docs->breadcrumb($documentation, $path));
            $fallback = $menuTitles[$path] ?? basename($path, '.md');

            $index[] = [
                'title' => $this->renderer->title($markdown, $fallback),
                'path' => $path,
                'url' => $this->docs->urlFor($documentation, $path),
                'breadcrumb' => implode(' / ', $breadcrumb),
                'text' => $this->renderer->plainText($markdown),
            ];
        }

        return $index;
    }

    private function excerpt(string $text, string $query): string
    {
        $position = mb_stripos($text, $query);

        if ($position === false) {
            return mb_substr($text, 0, 180);
        }

        $start = max(0, $position - 70);

        return trim(($start > 0 ? '...' : '') . mb_substr($text, $start, 180) . (mb_strlen($text) > $start + 180 ? '...' : ''));
    }

    private function cacheFile(string $documentation): string
    {
        return rtrim($this->cachePath, '/') . '/search-' . preg_replace('/[^A-Za-z0-9._-]/', '_', $documentation) . '.json';
    }
}
