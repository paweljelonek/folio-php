<?php

declare(strict_types=1);

namespace App\Http;

use App\Content\ContentLoaderInterface;
use App\Routing\RouteResolver;
use App\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\ErrorHandlerInterface;

final class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private readonly RouteResolver $resolver,
        private readonly ContentLoaderInterface $loader,
        private readonly TemplateRendererInterface $renderer,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        $code = $exception instanceof HttpNotFoundException ? 404 : 500;

        $response  = $this->responseFactory->createResponse($code);
        $errorPath = $this->resolver->resolveError($code);

        if ($errorPath !== null) {
            try {
                $page = $this->loader->load($errorPath);
                $html = $this->renderer->render($page);
                $response->getBody()->write($html);
                return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
            } catch (\Throwable) {
            }
        }

        $response->getBody()->write(sprintf('<h1>%d Error</h1>', $code));

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
