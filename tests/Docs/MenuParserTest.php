<?php

declare(strict_types=1);

namespace Bud\DocsApp\Tests\Docs;

use Bud\DocsApp\Docs\MenuParser;
use PHPUnit\Framework\TestCase;

final class MenuParserTest extends TestCase
{
    public function test_parses_title_from_first_heading(): void
    {
        $menu = (new MenuParser())->parse("# My Project\n\n- [Overview](index.md)");

        self::assertSame('My Project', $menu->title);
    }

    public function test_defaults_title_when_no_heading_present(): void
    {
        $menu = (new MenuParser())->parse("- [Overview](index.md)");

        self::assertSame('Documentation', $menu->title);
    }

    public function test_parses_flat_list_with_links(): void
    {
        $markdown = <<<MD
            # Project

            - [Overview](index.md)
            - [Quick start](quick-start.md)
            MD;

        $menu = (new MenuParser())->parse($markdown);

        self::assertCount(2, $menu->items);
        self::assertSame('Overview', $menu->items[0]->title);
        self::assertSame('index.md', $menu->items[0]->path);
        self::assertSame('Quick start', $menu->items[1]->title);
        self::assertSame('quick-start.md', $menu->items[1]->path);
    }

    public function test_parses_group_heading_without_link(): void
    {
        $markdown = <<<MD
            # Project

            - Integrations
              - [MCP server](integrations/mcp.md)
            MD;

        $menu = (new MenuParser())->parse($markdown);

        self::assertCount(1, $menu->items);
        self::assertSame('Integrations', $menu->items[0]->title);
        self::assertNull($menu->items[0]->path);
        self::assertCount(1, $menu->items[0]->children);
        self::assertSame('integrations/mcp.md', $menu->items[0]->children[0]->path);
    }

    public function test_parses_nested_levels(): void
    {
        $markdown = <<<MD
            # Project

            - [Overview](index.md)
            - Api
              - [Methods](api/methods.md)
              - Errors
                - [Codes](api/errors/codes.md)
            MD;

        $menu = (new MenuParser())->parse($markdown);

        self::assertCount(2, $menu->items);
        $api = $menu->items[1];
        self::assertSame('Api', $api->title);
        self::assertCount(2, $api->children);
        $errors = $api->children[1];
        self::assertSame('Errors', $errors->title);
        self::assertSame('api/errors/codes.md', $errors->children[0]->path);
    }

    public function test_child_without_matching_parent_level_falls_back_to_root(): void
    {
        // A line indented two levels deep with no level-1 item above it
        // should not crash the parser; it becomes a root item instead.
        $markdown = <<<MD
            # Project

              - [Orphan](orphan.md)
            MD;

        $menu = (new MenuParser())->parse($markdown);

        self::assertCount(1, $menu->items);
        self::assertSame('Orphan', $menu->items[0]->title);
    }

    public function test_ignores_non_list_lines(): void
    {
        $markdown = <<<MD
            # Project

            Some intro paragraph that is not a list item.

            - [Overview](index.md)
            MD;

        $menu = (new MenuParser())->parse($markdown);

        self::assertCount(1, $menu->items);
        self::assertSame('index.md', $menu->items[0]->path);
    }

    public function test_decodes_url_encoded_link_targets(): void
    {
        $markdown = "# Project\n\n- [Guide](getting%20started.md)";

        $menu = (new MenuParser())->parse($markdown);

        self::assertSame('getting started.md', $menu->items[0]->path);
    }
}
