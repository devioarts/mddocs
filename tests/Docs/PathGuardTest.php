<?php

declare(strict_types=1);

namespace Bud\DocsApp\Tests\Docs;

use Bud\DocsApp\Docs\PathGuard;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PathGuardTest extends TestCase
{
    public function test_documentation_accepts_valid_names(): void
    {
        self::assertSame('my-project', PathGuard::documentation('my-project'));
        self::assertSame('project_2', PathGuard::documentation(' project_2 '));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function invalidDocumentationNames(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace only' => ['   '];
        yield 'leading dot' => ['.hidden'];
        yield 'traversal' => ['../etc'];
        yield 'path separator' => ['docs/sub'];
        yield 'space inside' => ['my docs'];
        yield 'unicode' => ['dokumentácia'];
    }

    #[DataProvider('invalidDocumentationNames')]
    public function test_documentation_rejects_invalid_names(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        PathGuard::documentation($name);
    }

    public function test_page_path_defaults_to_index(): void
    {
        self::assertSame('index.md', PathGuard::pagePath(''));
        self::assertSame('index.md', PathGuard::pagePath('   '));
    }

    public function test_page_path_appends_markdown_extension(): void
    {
        self::assertSame('guide.md', PathGuard::pagePath('guide'));
        self::assertSame('guide.md', PathGuard::pagePath('guide.md'));
    }

    public function test_page_path_normalizes_backslashes_and_duplicate_slashes(): void
    {
        self::assertSame('a/b/c.md', PathGuard::pagePath('a\\b\\c'));
        self::assertSame('a/b.md', PathGuard::pagePath('/a//b/'));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function traversalAttempts(): iterable
    {
        yield 'parent segment' => ['../secret.md'];
        yield 'nested parent segment' => ['a/../../secret.md'];
        yield 'trailing parent segment' => ['a/..'];
        yield 'only parent segment' => ['..'];
        yield 'hidden segment' => ['.git/config.md'];
        yield 'hidden nested segment' => ['a/.hidden/b.md'];
    }

    #[DataProvider('traversalAttempts')]
    public function test_page_path_rejects_traversal_and_hidden_segments(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);
        PathGuard::pagePath($path);
    }

    public function test_page_path_allows_menu_md_as_a_regular_page(): void
    {
        // menu.md is an ordinary filename; only .menu.md is reserved for navigation.
        self::assertSame('menu.md', PathGuard::pagePath('menu.md'));
    }

    public function test_page_path_rejects_menu_file(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PathGuard::pagePath('.menu.md');
    }

    public function test_page_path_rejects_menu_file_in_any_subdirectory(): void
    {
        // basename() only looks at the final path segment, so this is
        // rejected too, not just .menu.md at the documentation root.
        $this->expectException(InvalidArgumentException::class);
        PathGuard::pagePath('sub/.menu.md');
    }

    public function test_asset_path_requires_assets_prefix(): void
    {
        self::assertSame('assets/diagram.png', PathGuard::assetPath('assets/diagram.png'));

        $this->expectException(InvalidArgumentException::class);
        PathGuard::assetPath('images/diagram.png');
    }

    public function test_asset_path_rejects_traversal_out_of_assets(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PathGuard::assetPath('assets/../secret.php');
    }

    public function test_menu_path_is_fixed(): void
    {
        self::assertSame('.menu.md', PathGuard::menuPath());
    }
}
