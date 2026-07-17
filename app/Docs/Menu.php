<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs;

final class Menu
{
    /**
     * @param list<MenuItem> $items
     */
    public function __construct(
        public readonly string $title,
        public readonly array $items,
    ) {
    }

    /**
     * @return list<MenuItem>
     */
    public function flattenPages(): array
    {
        $pages = [];
        $walk = static function (array $items) use (&$walk, &$pages): void {
            foreach ($items as $item) {
                if ($item->path !== null) {
                    $pages[] = $item;
                }

                $walk($item->children);
            }
        };

        $walk($this->items);

        return $pages;
    }
}
