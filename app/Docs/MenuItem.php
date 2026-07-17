<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs;

final class MenuItem
{
    /**
     * @param list<MenuItem> $children
     */
    public function __construct(
        public readonly string $title,
        public readonly ?string $path = null,
        public readonly array $children = [],
    ) {
    }

    /**
     * @return array{title: string, path: ?string, children: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'path' => $this->path,
            'children' => array_map(static fn (MenuItem $item): array => $item->toArray(), $this->children),
        ];
    }
}
