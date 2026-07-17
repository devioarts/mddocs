<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs;

final class MenuParser
{
    public function parse(string $markdown): Menu
    {
        $title = 'Documentation';
        $nodes = [];

        foreach (preg_split('/\R/', $markdown) ?: [] as $line) {
            if (preg_match('/^#\s+(.+)$/', trim($line), $match) === 1) {
                $title = trim($match[1]);
                break;
            }
        }

        $root = ['children' => []];
        $stack = [-1 => &$root];

        foreach (preg_split('/\R/', $markdown) ?: [] as $line) {
            if (preg_match('/^(\s*)-\s+(.+)$/', $line, $match) !== 1) {
                continue;
            }

            $level = intdiv(strlen(str_replace("\t", '    ', $match[1])), 2);
            $raw = trim($match[2]);
            [$itemTitle, $path] = $this->parseItem($raw);
            $node = [
                'title' => $itemTitle,
                'path' => $path,
                'children' => [],
            ];

            $parentLevel = $level - 1;

            if (! isset($stack[$parentLevel])) {
                $parentLevel = -1;
            }

            $stack[$parentLevel]['children'][] = $node;
            $lastIndex = array_key_last($stack[$parentLevel]['children']);
            $stack[$level] = &$stack[$parentLevel]['children'][$lastIndex];

            foreach (array_keys($stack) as $stackLevel) {
                if ($stackLevel > $level) {
                    unset($stack[$stackLevel]);
                }
            }
        }

        $nodes = $root['children'];

        return new Menu($title, $this->buildItems($nodes));
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private function parseItem(string $raw): array
    {
        if (preg_match('/^\[([^\]]+)\]\(([^)]+)\)$/', $raw, $match) === 1) {
            return [trim($match[1]), PathGuard::pagePath(rawurldecode(trim($match[2])))];
        }

        return [$raw, null];
    }

    /**
     * @param list<array{title: string, path: ?string, children: list<array<string, mixed>>}> $nodes
     *
     * @return list<MenuItem>
     */
    private function buildItems(array $nodes): array
    {
        $items = [];

        foreach ($nodes as $node) {
            $items[] = new MenuItem($node['title'], $node['path'], $this->buildItems($node['children']));
        }

        return $items;
    }
}
