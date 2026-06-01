<?php

declare(strict_types=1);

namespace App\Http;

use App\Cache\CacheInterface;
use App\Content\ContentLoaderInterface;
use App\Routing\RouteResolver;
use App\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

final class PageHandler
{
    public function __construct(
        private readonly RouteResolver $resolver,
        private readonly ContentLoaderInterface $loader,
        private readonly TemplateRendererInterface $renderer,
        private readonly CacheInterface $cache,
    ) {}

    /** @param array<string, mixed> $args */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $urlPath  = '/' . ltrim($args['path'] ?? '', '/');
        $cacheKey = 'page:' . $urlPath;

        $html = $this->cache->get($cacheKey);

        if ($html === null) {
            $filePath = $this->resolver->resolve($urlPath);

            if ($filePath === null) {
                return $this->notFound();
            }

            $page = $this->loader->load($filePath);
            $html = $this->renderer->render($page);

            $this->cache->set($cacheKey, $html);
        }

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    private function notFound(): ResponseInterface
    {
        $errorPath = $this->resolver->resolveError(404);
        $response  = new Response(404);

        if ($errorPath === null) {
            $response->getBody()->write('<h1>404 Not Found</h1>');
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        $page = $this->loader->load($errorPath);
        $html = $this->renderer->render($page);

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
