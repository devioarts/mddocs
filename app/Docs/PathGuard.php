<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs;

use InvalidArgumentException;

final class PathGuard
{
    private const MENU_FILENAME = '.menu.md';

    public static function documentation(string $documentation): string
    {
        $documentation = trim($documentation);

        if ($documentation === '' || str_starts_with($documentation, '.') || ! preg_match('/^[A-Za-z0-9._-]+$/', $documentation)) {
            throw new InvalidArgumentException('Invalid documentation name.');
        }

        return $documentation;
    }

    public static function pagePath(string $path): string
    {
        $path = trim(trim(str_replace('\\', '/', $path)), '/');
        $path = preg_replace('#/+#', '/', $path) ?? '';

        if ($path === '') {
            $path = 'index.md';
        }

        if (! str_ends_with($path, '.md')) {
            $path .= '.md';
        }

        if (basename($path) === self::MENU_FILENAME) {
            throw new InvalidArgumentException(self::MENU_FILENAME . ' must be edited through menu methods.');
        }

        self::assertRelativePath($path);

        return $path;
    }

    public static function menuPath(): string
    {
        return self::MENU_FILENAME;
    }

    public static function assetPath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        $path = preg_replace('#/+#', '/', $path) ?? '';
        self::assertRelativePath($path);

        if (! str_starts_with($path, 'assets/')) {
            throw new InvalidArgumentException('Assets must be stored below assets/.');
        }

        return $path;
    }

    private static function assertRelativePath(string $path): void
    {
        if ($path === '' || str_starts_with($path, '/') || str_contains($path, '../') || str_contains($path, '/..') || $path === '..') {
            throw new InvalidArgumentException('Path must stay inside the documentation directory.');
        }

        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.' || $part === '..' || str_starts_with($part, '.')) {
                throw new InvalidArgumentException('Hidden and traversal path segments are not allowed.');
            }
        }
    }
}
