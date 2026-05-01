<?php

declare(strict_types=1);

namespace App\Tests\Integration\Http;

use App\Cache\CacheInterface;
use App\Cache\NullCache;
use App\Content\FrontMatterParser;
use App\Content\MarkdownContentLoader;
use App\Http\PageHandler;
use App\Routing\RouteResolver;
use App\Template\TwigRenderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class PageHandlerTest extends TestCase
{
    private PageHandler $handler;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->fixturesDir = STUBS_DIR;

        $twig = new Environment(
            new FilesystemLoader($this->fixturesDir . '/templates'),
            ['cache' => false],
        );

        $resolver = new RouteResolver($this->fixturesDir . '/content');
        $loader   = new MarkdownContentLoader(new FrontMatterParser());
        $renderer = new TwigRenderer($twig);
        $cache    = new NullCache();

        $this->handler = new PageHandler($resolver, $loader, $renderer, $cache);
    }

    #[Test]
    public function returnsOkResponseForExistingPage(): void
    {
        $request  = $this->makeRequest('/');
        $response = ($this->handler)($request, (new ResponseFactory())->createResponse(), ['path' => '']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function rendersPageTitleInResponse(): void
    {
        $request  = $this->makeRequest('/');
        $response = ($this->handler)($request, (new ResponseFactory())->createResponse(), ['path' => '']);

        $body = (string) $response->getBody();

        $this->assertStringContainsString('Home', $body);
    }

    #[Test]
    public function rendersNestedPagePath(): void
    {
        $request  = $this->makeRequest('/sub/page');
        $response = ($this->handler)($request, (new ResponseFactory())->createResponse(), ['path' => 'sub/page']);

        $this->assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Sub Page', $body);
    }

    #[Test]
    public function returns404ResponseForMissingPage(): void
    {
        $request  = $this->makeRequest('/does-not-exist');
        $response = ($this->handler)($request, (new ResponseFactory())->createResponse(), ['path' => 'does-not-exist']);

        $this->assertSame(404, $response->getStatusCode());
    }

    #[Test]
    public function renders404ContentFromErrorFile(): void
    {
        $request  = $this->makeRequest('/does-not-exist');
        $response = ($this->handler)($request, (new ResponseFactory())->createResponse(), ['path' => 'does-not-exist']);

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Not Found', $body);
    }

    #[Test]
    public function servesFromCacheOnSecondRequest(): void
    {
        $cache = new class implements \App\Cache\CacheInterface {
            public array $stored = [];

            public function get(string $key): ?string
            {
                return $this->stored[$key] ?? null;
            }

            public function set(string $key, string $value, int $ttl = 0): void
            {
                $this->stored[$key] = $value;
            }

            public function delete(string $key): void
            {
                unset($this->stored[$key]);
            }

            public function clear(): void
            {
                $this->stored = [];
            }
        };

        $fixturesDir = $this->fixturesDir;
        $twig        = new Environment(new FilesystemLoader($fixturesDir . '/templates'), ['cache' => false]);
        $handler     = new PageHandler(
            new RouteResolver($fixturesDir . '/content'),
            new MarkdownContentLoader(new FrontMatterParser()),
            new TwigRenderer($twig),
            $cache,
        );

        $request = $this->makeRequest('/');
        $args    = ['path' => ''];

        ($handler)($request, (new ResponseFactory())->createResponse(), $args);

        $this->assertArrayHasKey('page:/', $cache->stored);

        $cachedHtml = $cache->stored['page:/'];
        $cache->stored['page:/'] = '<p>from cache</p>';

        $response2 = ($handler)($request, (new ResponseFactory())->createResponse(), $args);

        $this->assertStringContainsString('from cache', (string) $response2->getBody());
    }

    private function makeRequest(string $path): \Psr\Http\Message\ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest('GET', $path);
    }
}
