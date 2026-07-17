<?php

declare(strict_types=1);

namespace Bud\DocsApp\Mcp;

use Bud\DocsApp\Bootstrap;
use Mcp\Server;
use Mcp\Server\Session\FileSessionStore;

final class ServerFactory
{
    public static function build(?string $sessionPath = null): Server
    {
        $services = Bootstrap::services();
        $tools = new DocumentationTools(
            $services['docs'],
            $services['renderer'],
            $services['search'],
            (string) $services['config']['backup_path'],
            (string) $services['config']['audit_log'],
        );

        $builder = Server::builder()
            ->setServerInfo('PHP Markdown Docs', '0.1.0', 'Documentation editor and reader for Markdown files.')
            ->setInstructions('Use list_documentations first. When creating a page, update menu.md if the page should be visible in navigation. Only use Markdown pages and assets/ paths.')
            ->addTool(fn (): array => $tools->listDocumentations(), 'list_documentations', 'List available documentation roots.')
            ->addTool(fn (string $documentation): array => $tools->listPages($documentation), 'list_pages', 'List Markdown pages for one documentation root.')
            ->addTool(fn (string $documentation, string $path): array => $tools->getPage($documentation, $path), 'get_page', 'Read one Markdown page with title and outline.')
            ->addTool(fn (string $documentation, string $query): array => $tools->searchDocs($documentation, $query), 'search_docs', 'Search documentation content.')
            ->addTool(fn (string $documentation, string $path, string $content): array => $tools->createPage($documentation, $path, $content), 'create_page', 'Create a new Markdown page.')
            ->addTool(fn (string $documentation, string $path, string $content): array => $tools->updatePage($documentation, $path, $content), 'update_page', 'Replace an existing Markdown page.')
            ->addTool(fn (string $documentation, string $path, string $content): array => $tools->appendToPage($documentation, $path, $content), 'append_to_page', 'Append Markdown content to an existing page.')
            ->addTool(fn (string $documentation): string => $tools->getMenu($documentation), 'get_menu', 'Read menu.md for a documentation root.')
            ->addTool(fn (string $documentation, string $content): array => $tools->updateMenu($documentation, $content), 'update_menu', 'Replace menu.md for a documentation root.')
            ->addTool(fn (string $documentation, string $path, string $base64Content): array => $tools->uploadAsset($documentation, $path, $base64Content), 'upload_asset', 'Upload a base64 encoded asset below assets/.')
            ->addTool(fn (string $documentation): array => $tools->validateDocumentation($documentation), 'validate_documentation', 'Validate menu.md links against existing pages.')
            ->addTool(fn (string $documentation): array => $tools->rebuildSearchIndex($documentation), 'rebuild_search_index', 'Rebuild the JSON search index.');

        if ($sessionPath !== null) {
            $builder->setSession(new FileSessionStore($sessionPath));
        }

        return $builder->build();
    }
}
