<?php

declare(strict_types=1);

namespace Bud\DocsApp\Tests\Docs;

use Bud\DocsApp\Docs\MarkdownRenderer;
use PHPUnit\Framework\TestCase;

final class MarkdownRendererTest extends TestCase
{
    private MarkdownRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new MarkdownRenderer();
    }

    public function test_title_prefers_front_matter_over_heading(): void
    {
        $markdown = "---\ntitle: From Front Matter\n---\n\n# From Heading\n";

        self::assertSame('From Front Matter', $this->renderer->title($markdown, 'fallback'));
    }

    public function test_title_falls_back_to_first_heading(): void
    {
        self::assertSame('From Heading', $this->renderer->title("# From Heading\n\nBody", 'fallback'));
    }

    public function test_title_falls_back_to_provided_default(): void
    {
        self::assertSame('fallback', $this->renderer->title("Just a paragraph, no heading.", 'fallback'));
    }

    public function test_render_strips_raw_html_input(): void
    {
        $html = $this->renderer->render("# Title\n\n<script>alert('xss')</script>\n\nBody text.");

        self::assertStringNotContainsString('<script>', $html);
    }

    public function test_render_converts_basic_markdown(): void
    {
        $html = $this->renderer->render("# Title\n\nSome **bold** text.");

        self::assertStringContainsString('<strong>bold</strong>', $html);
    }

    public function test_outline_collects_headings_within_range(): void
    {
        $markdown = "# H1 not in outline\n\n## Second\n\n### Third\n\n##### Fifth not in outline\n";

        $outline = $this->renderer->outline($markdown);

        self::assertCount(2, $outline);
        self::assertSame('Second', $outline[0]['title']);
        self::assertSame(2, $outline[0]['level']);
        self::assertSame('Third', $outline[1]['title']);
        self::assertSame(3, $outline[1]['level']);
    }

    public function test_outline_deduplicates_slug_ids(): void
    {
        $markdown = "## Setup\n\n## Setup\n";

        $outline = $this->renderer->outline($markdown);

        self::assertSame('setup', $outline[0]['id']);
        self::assertSame('setup-1', $outline[1]['id']);
    }

    public function test_outline_id_matches_rendered_heading_id(): void
    {
        // Regression test: outline() must read the same id that render() actually
        // puts on the <hN> tag, not a separately computed slug. A heading with
        // inline code and a contraction is a case where two independent slug
        // algorithms (whitespace-only vs. all-punctuation separators) diverge.
        $markdown = "## Why `createElectronApp()` isn't a single black box\n";

        $outline = $this->renderer->outline($markdown);
        $html = $this->renderer->render($markdown);

        self::assertStringContainsString('id="' . $outline[0]['id'] . '"', $html);
        self::assertSame("Why createElectronApp() isn't a single black box", $outline[0]['title']);
    }

    public function test_plain_text_strips_code_fences_images_and_link_syntax(): void
    {
        $markdown = <<<MD
            # Title

            See the [docs](guide.md) and this ![diagram](diagram.png).

            ```php
            \$hidden = 'should not appear';
            ```

            Regular *text* here.
            MD;

        $text = $this->renderer->plainText($markdown);

        self::assertStringContainsString('See the docs and this', $text);
        self::assertStringContainsString('Regular text here', $text);
        self::assertStringNotContainsString('should not appear', $text);
        self::assertStringNotContainsString('](', $text);
    }

    public function test_metadata_returns_front_matter_as_array(): void
    {
        $markdown = "---\ntitle: Page\nversion: 2\n---\n\nBody";

        self::assertSame(['title' => 'Page', 'version' => 2], $this->renderer->metadata($markdown));
    }

    public function test_metadata_is_empty_without_front_matter(): void
    {
        self::assertSame([], $this->renderer->metadata("# Title\n\nBody"));
    }
}
