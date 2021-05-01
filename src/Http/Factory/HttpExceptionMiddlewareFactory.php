<?php
declare(strict_types = 1);

namespace Itseasy\Http\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Http\HttpExceptionMiddleware;

class HttpExceptionMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : HttpExceptionMiddleware
    {
        $config = $container->get("Config")->getConfig();

        $viewClass = $config["view"]["class"];
        $rendererClass = $config["view"]["renderer"];
        $error_layout = $config["view"]["error_layout"];

        $view = new $viewClass();
        $view->setRenderer($container->get($rendererClass));
        $view->setLayout($error_layout);

        return new HttpExceptionMiddleware($view);
    }
}
