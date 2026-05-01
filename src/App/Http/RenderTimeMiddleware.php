<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Stream;

final class RenderTimeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $elapsed = (microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000;

        $body = (string) $response->getBody();

        if (!str_contains($body, '</body>')) {
            return $response;
        }

        $info = sprintf(
            '<div style="position:fixed;bottom:0;left:0;right:0;background:rgba(0,0,0,.75);color:#ccc;font:11px/1 monospace;padding:6px 8px;text-align:center;z-index:9999">Page rendered in %.2f ms</div>',
            $elapsed,
        );

        $stream = new Stream(fopen('php://temp', 'r+b'));
        $stream->write(str_replace('</body>', $info . '</body>', $body));
        $stream->rewind();

        return $response->withBody($stream);
    }
}
