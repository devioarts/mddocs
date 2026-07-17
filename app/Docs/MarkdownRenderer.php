<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\NodeIterator;
use League\CommonMark\Node\RawMarkupContainerInterface;
use League\CommonMark\Node\StringContainerHelper;

final class MarkdownRenderer
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'insert' => 'none',
                'min_heading_level' => 1,
                'max_heading_level' => 4,
                'apply_id_to_heading' => true,
            ],
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    public function render(string $markdown): string
    {
        $parts = FrontMatter::split($markdown);

        return (string) $this->converter->convert($parts['body']);
    }

    /**
     * Reads the outline from the same parsed document that render() produces, so a
     * heading's outline id always matches the id actually rendered on the <hN> tag
     * (HeadingPermalinkExtension and this method must not compute slugs independently).
     *
     * @return list<array{level: int, title: string, id: string}>
     */
    public function outline(string $markdown): array
    {
        $parts = FrontMatter::split($markdown);
        $document = $this->converter->convert($parts['body'])->getDocument();
        $outline = [];

        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if (! $node instanceof Heading || $node->getLevel() < 2 || $node->getLevel() > 4) {
                continue;
            }

            $outline[] = [
                'level' => $node->getLevel(),
                'title' => StringContainerHelper::getChildText($node, [RawMarkupContainerInterface::class]),
                'id' => (string) $node->data->get('attributes/id'),
            ];
        }

        return $outline;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(string $markdown): array
    {
        return FrontMatter::split($markdown)['metadata'];
    }

    public function title(string $markdown, string $fallback): string
    {
        $parts = FrontMatter::split($markdown);

        if (isset($parts['metadata']['title']) && is_string($parts['metadata']['title'])) {
            return $parts['metadata']['title'];
        }

        if (preg_match('/^#\s+(.+)$/m', $parts['body'], $match) === 1) {
            return trim($match[1]);
        }

        return $fallback;
    }

    public function plainText(string $markdown): string
    {
        $parts = FrontMatter::split($markdown);
        $text = preg_replace('/```.*?```/s', ' ', $parts['body']) ?? $parts['body'];
        $text = preg_replace('/!\[[^\]]*]\([^)]+\)/', ' ', $text) ?? $text;
        $text = preg_replace('/\[([^\]]+)]\([^)]+\)/', '$1', $text) ?? $text;
        $text = preg_replace('/[#>*_`~\-\[\]()|]/', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }
}
