<?php

declare(strict_types=1);

namespace App\Tests\Unit\Routing;

use App\Routing\RouteResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RouteResolverTest extends TestCase
{
    private RouteResolver $resolver;
    private string $contentDir;

    protected function setUp(): void
    {
        $this->contentDir = STUBS_DIR . '/content';
        $this->resolver   = new RouteResolver($this->contentDir);
    }

    #[Test]
    #[DataProvider('validRouteProvider')]
    public function resolvesUrlToFilePath(string $url, string $expectedFile): void
    {
        $result = $this->resolver->resolve($url);

        $this->assertSame(realpath($this->contentDir . '/' . $expectedFile), $result);
    }

    /** @return array<string, array{string, string}> */
    public static function validRouteProvider(): array
    {
        return [
            'root'           => ['/',         'index.md'],
            'empty string'   => ['',          'index.md'],
            'simple page'    => ['/about',    'about.md'],
            'without slash'  => ['about',     'about.md'],
            'nested path'    => ['/sub/page', 'sub/page.md'],
            'trailing slash' => ['/about/',   'about.md'],
        ];
    }

    #[Test]
    public function returnsNullForMissingFile(): void
    {
        $this->assertNull($this->resolver->resolve('/does-not-exist'));
    }

    #[Test]
    #[DataProvider('blockedUrlProvider')]
    public function blocksUnsafeUrls(string $url): void
    {
        $this->assertNull($this->resolver->resolve($url));
    }

    /** @return array<string, array{string}> */
    public static function blockedUrlProvider(): array
    {
        return [
            'double dot'               => ['/../etc/passwd'],
            'embedded double dot'      => ['/foo/../bar'],
            'null byte'                => ["/foo\0bar"],
            'single dot segment'       => ['/./about'],
            'empty segment from slash' => ['/foo//bar'],
            'underscore dir via url'   => ['/_errors/404'],
            'underscore segment'       => ['/_hidden'],
        ];
    }

    #[Test]
    public function resolvesErrorPage(): void
    {
        $result = $this->resolver->resolveError(404);

        $this->assertSame(realpath($this->contentDir . '/_errors/404.md'), $result);
    }

    #[Test]
    public function returnsNullForMissingErrorPage(): void
    {
        $this->assertNull($this->resolver->resolveError(500));
    }
}
