<?php

declare(strict_types=1);

namespace App\Tests\Integration\Http;

use App\Content\FrontMatterParser;
use App\Content\MarkdownContentLoader;
use App\Http\ErrorHandler;
use App\Routing\RouteResolver;
use App\Template\TemplateRendererInterface;
use App\Template\TwigRenderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class ErrorHandlerTest extends TestCase
{
    private ErrorHandler $handler;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->responseFactory = new ResponseFactory();

        $this->handler = $this->makeHandler(STUBS_DIR . '/content');
    }

    #[Test]
    public function returns404StatusForHttpNotFoundException(): void
    {
        $response = $this->invoke(new HttpNotFoundException($this->makeRequest()));

        $this->assertSame(404, $response->getStatusCode());
    }

    #[Test]
    public function returns500StatusForGenericException(): void
    {
        $response = $this->invoke(new \RuntimeException('Something went wrong'));

        $this->assertSame(500, $response->getStatusCode());
    }

    #[Test]
    public function rendersCustom404PageFromErrorFile(): void
    {
        $response = $this->invoke(new HttpNotFoundException($this->makeRequest()));

        $this->assertStringContainsString('Not Found', (string) $response->getBody());
    }

    #[Test]
    public function fallsBackToPlainHtmlWhenNoErrorFileExists(): void
    {
        $emptyDir = sys_get_temp_dir() . '/folio_empty_' . uniqid();
        mkdir($emptyDir, 0755);

        try {
            $response = ($this->makeHandler($emptyDir))(
                $this->makeRequest(),
                new \RuntimeException('boom'),
                false, false, false,
            );

            $this->assertSame(500, $response->getStatusCode());
            $this->assertStringContainsString('500', (string) $response->getBody());
            $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
        } finally {
            rmdir($emptyDir);
        }
    }

    #[Test]
    public function fallsBackToPlainHtmlWhenRenderingFails(): void
    {
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer->method('render')->willThrowException(new \RuntimeException('Render failed'));

        $handler = new ErrorHandler(
            new RouteResolver(STUBS_DIR . '/content'),
            new MarkdownContentLoader(new FrontMatterParser()),
            $renderer,
            $this->responseFactory,
        );

        $response = ($handler)(
            $this->makeRequest(),
            new HttpNotFoundException($this->makeRequest()),
            false, false, false,
        );

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('404', (string) $response->getBody());
    }

    private function invoke(\Throwable $exception): ResponseInterface
    {
        return ($this->handler)($this->makeRequest(), $exception, false, false, false);
    }

    private function makeHandler(string $contentDir): ErrorHandler
    {
        $twig = new Environment(
            new FilesystemLoader(STUBS_DIR . '/templates'),
            ['cache' => false],
        );

        return new ErrorHandler(
            new RouteResolver($contentDir),
            new MarkdownContentLoader(new FrontMatterParser()),
            new TwigRenderer($twig),
            $this->responseFactory,
        );
    }

    private function makeRequest(): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest('GET', '/');
    }
}
