<?php

declare(strict_types=1);

namespace Bud\DocsApp\Tests\Docs;

use Bud\DocsApp\Docs\DocumentationService;
use Bud\DocsApp\Docs\MenuParser;
use Bud\DocsApp\Docs\Storage\LocalFilesystemStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class DocumentationServiceTest extends TestCase
{
    private string $root;
    private DocumentationService $service;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/mddocs-test-' . bin2hex(random_bytes(6));
        $storage = new LocalFilesystemStorage($this->root);
        $this->service = new DocumentationService($storage, new MenuParser());
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->root);
    }

    private function writePage(string $documentation, string $path, string $content): void
    {
        $this->service->writePage($documentation, $path, $content);
    }

    private function writeMenu(string $documentation, string $content): void
    {
        $this->service->writeMenu($documentation, $content);
    }

    public function test_url_for_index_page_has_no_slug(): void
    {
        $this->writePage('demo', 'index.md', '# Demo');

        self::assertSame('/demo', $this->service->urlFor('demo', 'index.md'));
    }

    public function test_url_for_other_page_includes_slug(): void
    {
        $this->writePage('demo', 'index.md', '# Demo');
        $this->writePage('demo', 'guide.md', '# Guide');

        self::assertSame('/demo/guide', $this->service->urlFor('demo', 'guide.md'));
    }

    public function test_previous_next_at_first_and_last_page(): void
    {
        $this->writePage('demo', 'index.md', '# Demo');
        $this->writePage('demo', 'guide.md', '# Guide');
        $this->writeMenu('demo', "# Demo\n\n- [Overview](index.md)\n- [Guide](guide.md)\n");

        $first = $this->service->previousNext('demo', 'index.md');
        self::assertNull($first['prev']);
        self::assertSame('guide.md', $first['next']?->path);

        $last = $this->service->previousNext('demo', 'guide.md');
        self::assertSame('index.md', $last['prev']?->path);
        self::assertNull($last['next']);
    }

    public function test_breadcrumb_follows_nested_menu(): void
    {
        $this->writePage('demo', 'index.md', '# Demo');
        $this->writePage('demo', 'integrations/mcp.md', '# MCP');
        $this->writeMenu('demo', "# Demo\n\n- [Overview](index.md)\n- Integrations\n  - [MCP](integrations/mcp.md)\n");

        $trail = $this->service->breadcrumb('demo', 'integrations/mcp.md');

        self::assertCount(2, $trail);
        self::assertSame('Integrations', $trail[0]->title);
        self::assertSame('MCP', $trail[1]->title);
    }

    public function test_validate_reports_missing_menu_target(): void
    {
        $this->writePage('demo', 'index.md', '# Demo');
        $this->writeMenu('demo', "# Demo\n\n- [Overview](index.md)\n- [Missing](missing.md)\n");

        $errors = $this->service->validate('demo');

        self::assertCount(1, $errors);
        self::assertStringContainsString('missing.md', $errors[0]);
    }

    public function test_validate_passes_when_all_menu_links_exist(): void
    {
        $this->writePage('demo', 'index.md', '# Demo');
        $this->writeMenu('demo', "# Demo\n\n- [Overview](index.md)\n");

        self::assertSame([], $this->service->validate('demo'));
    }

    public function test_generated_menu_is_used_when_menu_file_is_missing(): void
    {
        $this->writePage('demo', 'index.md', '# Demo Overview');
        $this->writePage('demo', 'guide.md', '# The Guide');

        $menu = $this->service->menu('demo');

        self::assertSame('Demo', $menu->title);
        $titles = array_map(static fn ($item) => $item->title, $menu->items);
        self::assertContains('Demo Overview', $titles);
        self::assertContains('The Guide', $titles);
    }

    public function test_resolve_page_path_defaults_to_index_for_empty_slug(): void
    {
        $this->writePage('demo', 'index.md', '# Demo');

        self::assertSame('index.md', $this->service->resolvePagePath('demo', null));
        self::assertSame('index.md', $this->service->resolvePagePath('demo', ''));
    }

    public function test_resolve_page_path_uses_readme_when_no_index(): void
    {
        $this->writePage('demo', 'README.md', '# Demo');

        self::assertSame('README.md', $this->service->resolvePagePath('demo', ''));
    }
}
