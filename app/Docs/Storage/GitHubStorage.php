<?php

declare(strict_types=1);

namespace Bud\DocsApp\Docs\Storage;

use Bud\DocsApp\Docs\PathGuard;
use GuzzleHttp\Client;
use RuntimeException;

final class GitHubStorage implements DocumentationStorage
{
    private Client $client;

    /** @var array<string, string> */
    private array $config;

    /**
     * @param array<string, string> $config
     */
    public function __construct(array $config)
    {
        foreach (['owner', 'repo', 'branch', 'docs_path', 'token'] as $key) {
            if (($config[$key] ?? '') === '') {
                throw new RuntimeException(sprintf('Missing GitHub configuration value "%s".', $key));
            }
        }

        $this->config = $config;
        $this->client = new Client([
            'base_uri' => 'https://api.github.com',
            'headers' => [
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $config['token'],
                'X-GitHub-Api-Version' => '2022-11-28',
                'User-Agent' => 'mddocs',
            ],
            'http_errors' => false,
        ]);
    }

    public function listDocumentations(): array
    {
        $items = $this->request('GET', $this->contentsPath($this->config['docs_path']));
        $documentations = [];

        foreach ($items as $item) {
            if (($item['type'] ?? '') === 'dir') {
                $documentations[] = (string) $item['name'];
            }
        }

        sort($documentations);

        return $documentations;
    }

    public function documentationExists(string $documentation): bool
    {
        return $this->listMarkdownFiles($documentation) !== [];
    }

    public function readMenu(string $documentation): string
    {
        return $this->readFile($documentation, PathGuard::menuPath());
    }

    public function writeMenu(string $documentation, string $content): void
    {
        $this->writeFile($documentation, PathGuard::menuPath(), $content, 'docs: update menu');
    }

    public function listMarkdownFiles(string $documentation): array
    {
        $documentation = PathGuard::documentation($documentation);
        $prefix = trim($this->config['docs_path'] . '/' . $documentation, '/') . '/';
        $tree = $this->request('GET', sprintf('/repos/%s/%s/git/trees/%s?recursive=1', $this->config['owner'], $this->config['repo'], rawurlencode($this->config['branch'])));
        $files = [];

        foreach (($tree['tree'] ?? []) as $node) {
            $path = (string) ($node['path'] ?? '');

            if (($node['type'] ?? '') !== 'blob' || ! str_starts_with($path, $prefix) || ! str_ends_with($path, '.md')) {
                continue;
            }

            $relative = substr($path, strlen($prefix));

            if ($relative !== 'menu.md') {
                $files[] = $relative;
            }
        }

        sort($files);

        return $files;
    }

    public function pageExists(string $documentation, string $path): bool
    {
        try {
            $this->readPage($documentation, $path);
            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    public function readPage(string $documentation, string $path): string
    {
        return $this->readFile($documentation, PathGuard::pagePath($path));
    }

    public function writePage(string $documentation, string $path, string $content): void
    {
        $path = PathGuard::pagePath($path);
        $this->writeFile($documentation, $path, $content, sprintf('docs: update %s', $path));
    }

    public function readAsset(string $documentation, string $path): string
    {
        return $this->readFile($documentation, PathGuard::assetPath($path));
    }

    public function uploadAsset(string $documentation, string $path, string $binaryContent): void
    {
        $path = PathGuard::assetPath($path);
        $this->writeFile($documentation, $path, $binaryContent, sprintf('docs: upload %s', $path));
    }

    private function readFile(string $documentation, string $path): string
    {
        $response = $this->request('GET', $this->contentsPath($this->documentPath($documentation, $path)));

        if (($response['encoding'] ?? '') !== 'base64' || ! isset($response['content'])) {
            throw new RuntimeException(sprintf('GitHub file "%s" did not contain base64 content.', $path));
        }

        $content = base64_decode((string) $response['content'], true);

        if ($content === false) {
            throw new RuntimeException(sprintf('GitHub file "%s" could not be decoded.', $path));
        }

        return $content;
    }

    private function writeFile(string $documentation, string $path, string $content, string $message): void
    {
        $sha = null;

        try {
            $existing = $this->request('GET', $this->contentsPath($this->documentPath($documentation, $path)));
            $sha = $existing['sha'] ?? null;
        } catch (RuntimeException) {
            $sha = null;
        }

        $payload = [
            'message' => $message,
            'content' => base64_encode($content),
            'branch' => $this->config['branch'],
            'committer' => [
                'name' => $this->config['committer_name'] ?? 'Docs MCP',
                'email' => $this->config['committer_email'] ?? 'docs-mcp@example.com',
            ],
        ];

        if (is_string($sha)) {
            $payload['sha'] = $sha;
        }

        $this->request('PUT', $this->contentsPath($this->documentPath($documentation, $path), false), ['json' => $payload]);
    }

    private function documentPath(string $documentation, string $path): string
    {
        return trim($this->config['docs_path'] . '/' . PathGuard::documentation($documentation) . '/' . $path, '/');
    }

    private function contentsPath(string $path, bool $withRef = true): string
    {
        $uri = sprintf('/repos/%s/%s/contents/%s', $this->config['owner'], $this->config['repo'], str_replace('%2F', '/', rawurlencode($path)));

        return $withRef ? $uri . '?ref=' . rawurlencode($this->config['branch']) : $uri;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    private function request(string $method, string $uri, array $options = []): array
    {
        $response = $this->client->request($method, $uri, $options);
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        if ($response->getStatusCode() >= 400) {
            $message = is_array($decoded) ? ($decoded['message'] ?? $body) : $body;
            throw new RuntimeException(sprintf('GitHub API error %d: %s', $response->getStatusCode(), $message));
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('GitHub API returned an unexpected response.');
        }

        return $decoded;
    }
}
