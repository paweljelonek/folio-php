<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Http\RenderTimeMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;

final class RenderTimeMiddlewareTest extends TestCase
{
    private RenderTimeMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new RenderTimeMiddleware();
    }

    #[Test]
    public function injectsRenderTimeBeforeClosingBodyTag(): void
    {
        $handler = $this->handlerReturning('<html><body><p>Hello</p></body></html>');

        $response = $this->middleware->process($this->makeRequest(), $handler);

        $body = (string) $response->getBody();
        $this->assertStringContainsString('Page rendered in', $body);
        $this->assertStringContainsString('ms', $body);
        $this->assertMatchesRegularExpression('/<\/div><\/body>/', $body);
    }

    #[Test]
    public function renderTimeAppearsBeforeClosingBodyTag(): void
    {
        $handler = $this->handlerReturning('<html><body><p>content</p></body></html>');

        $response = $this->middleware->process($this->makeRequest(), $handler);

        $body = (string) $response->getBody();
        $this->assertLessThan(strrpos($body, '</body>'), strpos($body, 'Page rendered in'));
    }

    #[Test]
    public function returnsResponseUnchangedWhenNoBodyTag(): void
    {
        $original = 'no body tag here';
        $handler  = $this->handlerReturning($original);

        $response = $this->middleware->process($this->makeRequest(), $handler);

        $this->assertSame($original, (string) $response->getBody());
    }

    #[Test]
    public function preservesOriginalResponseStatusAndHeaders(): void
    {
        $handler = $this->handlerWithStatus('<html><body></body></html>', 200, 'text/html; charset=utf-8');

        $response = $this->middleware->process($this->makeRequest(), $handler);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function usesRequestTimeFloatAsStartTime(): void
    {
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true) - 0.5;

        $handler  = $this->handlerReturning('<html><body></body></html>');
        $response = $this->middleware->process($this->makeRequest(), $handler);

        $body = (string) $response->getBody();

        preg_match('/Page rendered in ([\d.]+) ms/', $body, $matches);
        $this->assertNotEmpty($matches);
        $this->assertGreaterThanOrEqual(500.0, (float) $matches[1]);
    }

    private function handlerReturning(string $html): RequestHandlerInterface
    {
        return $this->handlerWithStatus($html, 200, 'text/html; charset=utf-8');
    }

    private function handlerWithStatus(string $html, int $status, string $contentType): RequestHandlerInterface
    {
        return new class($html, $status, $contentType) implements RequestHandlerInterface {
            public function __construct(
                private readonly string $html,
                private readonly int $status,
                private readonly string $contentType,
            ) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $stream = new Stream(fopen('php://temp', 'r+b'));
                $stream->write($this->html);
                $stream->rewind();

                return (new ResponseFactory())
                    ->createResponse($this->status)
                    ->withHeader('Content-Type', $this->contentType)
                    ->withBody($stream);
            }
        };
    }

    private function makeRequest(): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest('GET', '/');
    }
}
