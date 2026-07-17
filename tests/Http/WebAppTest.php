<?php

declare(strict_types=1);

namespace Bud\DocsApp\Tests\Http;

use Bud\DocsApp\Docs\DocumentationService;
use Bud\DocsApp\Docs\MarkdownRenderer;
use Bud\DocsApp\Docs\MenuParser;
use Bud\DocsApp\Docs\SearchService;
use Bud\DocsApp\Docs\Storage\LocalFilesystemStorage;
use Bud\DocsApp\Http\WebApp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class WebAppTest extends TestCase
{
    private string $root;
    private string $cachePath;
    private string $errorLog;
    private DocumentationService $docs;
    private MarkdownRenderer $renderer;
    private SearchService $search;

    protected function setUp(): void
    {
        $base = sys_get_temp_dir() . '/mddocs-webapp-test-' . bin2hex(random_bytes(6));
        $this->root = $base . '/docs';
        $this->cachePath = $base . '/cache';
        $this->errorLog = $base . '/log/app-error.log';

        $storage = new LocalFilesystemStorage($this->root);
        $this->docs = new DocumentationService($storage, new MenuParser());
        $this->renderer = new MarkdownRenderer();
        $this->search = new SearchService($this->docs, $this->renderer, $this->cachePath);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove(dirname($this->root));
    }

    private function webApp(bool $debug = false): WebApp
    {
        return new WebApp($this->docs, $this->renderer, $this->search, $debug, $this->errorLog);
    }

    private function render(WebApp $app, string $method, string $uri): string
    {
        ob_start();
        $app->handle($method, $uri);

        return (string) ob_get_clean();
    }

    public function test_rewrites_relative_markdown_links_to_documentation_urls(): void
    {
        $this->docs->writePage('demo', 'index.md', "# Demo\n\nSee the [guide](sub/guide.md).");
        $this->docs->writePage('demo', 'sub/guide.md', '# Guide');

        $html = $this->render($this->webApp(), 'GET', '/demo');

        self::assertStringContainsString('href="/demo/sub/guide"', $html);
    }

    public function test_rewrites_relative_asset_image_src_to_asset_route(): void
    {
        $this->docs->writePage('demo', 'index.md', "# Demo\n\n![diagram](assets/diagram.png)");

        $html = $this->render($this->webApp(), 'GET', '/demo');

        self::assertStringContainsString('src="/_asset/demo/assets/diagram.png"', $html);
    }

    public function test_leaves_external_and_anchor_links_untouched(): void
    {
        $this->docs->writePage('demo', 'index.md', "# Demo\n\n[External](https://example.com) and [anchor](#section).");

        $html = $this->render($this->webApp(), 'GET', '/demo');

        self::assertStringContainsString('href="https://example.com"', $html);
        self::assertStringContainsString('href="#section"', $html);
    }

    public function test_unknown_documentation_returns_404(): void
    {
        $this->render($this->webApp(), 'GET', '/does-not-exist');

        self::assertSame(404, http_response_code());
    }

    public function test_error_response_hides_exception_message_when_debug_disabled(): void
    {
        $html = $this->render($this->webApp(debug: false), 'GET', '/..%2f..%2fetc');

        self::assertSame(500, http_response_code());
        self::assertStringNotContainsString('Invalid documentation name', $html);
        self::assertStringContainsString('Something went wrong', $html);
    }

    public function test_error_response_shows_exception_message_when_debug_enabled(): void
    {
        $html = $this->render($this->webApp(debug: true), 'GET', '/..%2f..%2fetc');

        self::assertSame(500, http_response_code());
        self::assertStringContainsString('Invalid documentation name', $html);
    }

    public function test_error_is_written_to_error_log(): void
    {
        self::assertFileDoesNotExist($this->errorLog);

        $this->render($this->webApp(), 'GET', '/..%2f..%2fetc');

        self::assertFileExists($this->errorLog);
        $entry = json_decode((string) file_get_contents($this->errorLog), true);
        self::assertIsArray($entry);
        self::assertStringContainsString('Invalid documentation name', $entry['message']);
    }
}
