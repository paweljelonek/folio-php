<?php

declare(strict_types=1);

use App\Cache\CacheFactory;
use App\Cache\CacheInterface;
use App\Content\ContentLoaderInterface;
use App\Content\FrontMatterParser;
use App\Content\MarkdownContentLoader;
use App\Http\ErrorHandler;
use App\Http\PageHandler;
use App\Routing\RouteResolver;
use App\Template\TemplateRendererInterface;
use App\Template\TwigRenderer;
use DI\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$baseDir = $_ENV['APP_BASE_DIR'] ?? dirname(__DIR__);

return [

    // --- Environment configuration ---

    'app.debug' => static fn() => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),

    'app.show_render_time' => static fn() => filter_var($_ENV['SHOW_RENDER_TIME'] ?? false, FILTER_VALIDATE_BOOLEAN),

    'app.content_path' => static fn() => rtrim(
        str_starts_with($_ENV['CONTENT_PATH'] ?? 'content', '/')
            ? $_ENV['CONTENT_PATH']
            : $baseDir . '/' . ($_ENV['CONTENT_PATH'] ?? 'content'),
        '/',
    ),

    'app.templates_path' => static fn() => rtrim(
        str_starts_with($_ENV['TEMPLATES_PATH'] ?? 'resources/templates', '/')
            ? $_ENV['TEMPLATES_PATH']
            : $baseDir . '/' . ($_ENV['TEMPLATES_PATH'] ?? 'resources/templates'),
        '/',
    ),

    'app.cache_driver' => static fn() => $_ENV['CACHE_DRIVER'] ?? 'null',

    'app.cache_dir' => static fn() => rtrim(
        str_starts_with($_ENV['CACHE_DIR'] ?? 'var/cache', '/')
            ? $_ENV['CACHE_DIR']
            : $baseDir . '/' . ($_ENV['CACHE_DIR'] ?? 'var/cache'),
        '/',
    ),

    'app.cache_ttl' => static fn() => (int) ($_ENV['CACHE_TTL'] ?? 0),

    // --- Infrastructure ---

    ResponseFactoryInterface::class => static fn() => new ResponseFactory(),

    Environment::class => static function (Container $c): Environment {
        $loader = new FilesystemLoader($c->get('app.templates_path'));

        return new Environment($loader, [
            'debug' => $c->get('app.debug'),
            'cache' => false,
        ]);
    },

    CacheInterface::class => static function (Container $c): CacheInterface {
        return (new CacheFactory())->create(
            $c->get('app.cache_driver'),
            [
                'cache_dir' => $c->get('app.cache_dir'),
                'ttl'       => $c->get('app.cache_ttl'),
            ],
        );
    },

    // --- Content ---

    FrontMatterParser::class => static fn() => new FrontMatterParser(),

    ContentLoaderInterface::class => static fn(Container $c) => new MarkdownContentLoader(
        $c->get(FrontMatterParser::class),
    ),

    // --- Routing ---

    RouteResolver::class => static fn(Container $c) => new RouteResolver(
        $c->get('app.content_path'),
    ),

    // --- Template ---

    TemplateRendererInterface::class => static fn(Container $c) => new TwigRenderer(
        $c->get(Environment::class),
    ),

    // --- HTTP Handlers ---

    PageHandler::class => static fn(Container $c) => new PageHandler(
        resolver:  $c->get(RouteResolver::class),
        loader:    $c->get(ContentLoaderInterface::class),
        renderer:  $c->get(TemplateRendererInterface::class),
        cache:     $c->get(CacheInterface::class),
    ),

    ErrorHandler::class => static fn(Container $c) => new ErrorHandler(
        resolver:        $c->get(RouteResolver::class),
        loader:          $c->get(ContentLoaderInterface::class),
        renderer:        $c->get(TemplateRendererInterface::class),
        responseFactory: $c->get(ResponseFactoryInterface::class),
    ),

];
