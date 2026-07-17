<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs\Storage;

use Bud\DocsApp\Docs\PathGuard;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

final class LocalFilesystemStorage implements DocumentationStorage
{
    private Filesystem $filesystem;

    public function __construct(private readonly string $rootPath)
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->rootPath);
    }

    public function listDocumentations(): array
    {
        $items = [];

        foreach (scandir($this->rootPath) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }

            if (is_dir($this->rootPath . '/' . $entry) && $this->containsDocumentation($entry)) {
                $items[] = $entry;
            }
        }

        sort($items);

        return $items;
    }

    public function documentationExists(string $documentation): bool
    {
        $documentation = PathGuard::documentation($documentation);

        return is_dir($this->documentRoot($documentation)) && $this->containsDocumentation($documentation);
    }

    public function readMenu(string $documentation): string
    {
        return $this->readFile($documentation, PathGuard::menuPath());
    }

    public function writeMenu(string $documentation, string $content): void
    {
        $this->writeFile($documentation, PathGuard::menuPath(), $content);
    }

    public function listMarkdownFiles(string $documentation): array
    {
        $documentation = PathGuard::documentation($documentation);
        $root = $this->documentRoot($documentation);

        if (! is_dir($root)) {
            return [];
        }

        $files = [];
        $finder = (new Finder())->files()->in($root)->name('*.md')->notName('menu.md')->ignoreDotFiles(true);

        foreach ($finder as $file) {
            $files[] = str_replace('\\', '/', $file->getRelativePathname());
        }

        sort($files);

        return $files;
    }

    public function pageExists(string $documentation, string $path): bool
    {
        $path = PathGuard::pagePath($path);

        return is_file($this->documentRoot(PathGuard::documentation($documentation)) . '/' . $path);
    }

    public function readPage(string $documentation, string $path): string
    {
        return $this->readFile($documentation, PathGuard::pagePath($path));
    }

    public function writePage(string $documentation, string $path, string $content): void
    {
        $this->writeFile($documentation, PathGuard::pagePath($path), $content);
    }

    public function readAsset(string $documentation, string $path): string
    {
        return $this->readFile($documentation, PathGuard::assetPath($path));
    }

    public function uploadAsset(string $documentation, string $path, string $binaryContent): void
    {
        $this->writeFile($documentation, PathGuard::assetPath($path), $binaryContent);
    }

    private function readFile(string $documentation, string $path): string
    {
        $file = $this->documentRoot(PathGuard::documentation($documentation)) . '/' . $path;

        if (! is_file($file)) {
            throw new RuntimeException(sprintf('File "%s" was not found.', $path));
        }

        $content = file_get_contents($file);

        if ($content === false) {
            throw new RuntimeException(sprintf('File "%s" could not be read.', $path));
        }

        return $content;
    }

    private function writeFile(string $documentation, string $path, string $content): void
    {
        $file = $this->documentRoot(PathGuard::documentation($documentation)) . '/' . $path;
        $this->filesystem->mkdir(dirname($file));

        if (file_put_contents($file, $content) === false) {
            throw new RuntimeException(sprintf('File "%s" could not be written.', $path));
        }
    }

    private function documentRoot(string $documentation): string
    {
        return rtrim($this->rootPath, '/') . '/' . $documentation;
    }

    private function containsDocumentation(string $documentation): bool
    {
        $root = $this->documentRoot(PathGuard::documentation($documentation));

        if (! is_dir($root)) {
            return false;
        }

        if (is_file($root . '/menu.md')) {
            return true;
        }

        return $this->listMarkdownFiles($documentation) !== [];
    }
}
