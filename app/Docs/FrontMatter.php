<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs;

use Symfony\Component\Yaml\Yaml;

final class FrontMatter
{
    /**
     * @return array{metadata: array<string, mixed>, body: string}
     */
    public static function split(string $markdown): array
    {
        if (preg_match('/\A---\R(.*?)\R---\R(.*)\z/s', $markdown, $match) !== 1) {
            return ['metadata' => [], 'body' => $markdown];
        }

        $metadata = Yaml::parse($match[1]);

        return [
            'metadata' => is_array($metadata) ? $metadata : [],
            'body' => $match[2],
        ];
    }
}
