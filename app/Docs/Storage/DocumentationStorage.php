<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs\Storage;

interface DocumentationStorage
{
    /**
     * @return list<string>
     */
    public function listDocumentations(): array;

    public function documentationExists(string $documentation): bool;

    public function readMenu(string $documentation): string;

    public function writeMenu(string $documentation, string $content): void;

    /**
     * @return list<string>
     */
    public function listMarkdownFiles(string $documentation): array;

    public function pageExists(string $documentation, string $path): bool;

    public function readPage(string $documentation, string $path): string;

    public function writePage(string $documentation, string $path, string $content): void;

    public function readAsset(string $documentation, string $path): string;

    public function uploadAsset(string $documentation, string $path, string $binaryContent): void;
}
