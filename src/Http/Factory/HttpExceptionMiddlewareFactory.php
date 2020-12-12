<?php

namespace App\Http\Factory;

use Psr\Container\ContainerInterface;
use App\Http\HttpExceptionMiddleware;

class HttpExceptionMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
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
