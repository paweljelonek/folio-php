<?php

declare(strict_types=1);

use App\Http\ErrorHandler;
use App\Http\PageHandler;
use App\Http\RenderTimeMiddleware;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->load(dirname(__DIR__) . '/.env');

$builder = new ContainerBuilder();
$builder->addDefinitions(require dirname(__DIR__) . '/config/container.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();

if ($container->get('app.show_render_time')) {
    $app->add(RenderTimeMiddleware::class);
}

$errorMiddleware = $app->addErrorMiddleware(
    displayErrorDetails: (bool) $container->get('app.debug'),
    logErrors:           true,
    logErrorDetails:     true,
);
$errorMiddleware->setDefaultErrorHandler($container->get(ErrorHandler::class));

$app->get('/{path:.*}', PageHandler::class);

$app->run();
