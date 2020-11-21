<?php

namespace App\Middleware\Factory;

use App\Middleware\HttpRendererMiddleware;
use Psr\Container\ContainerInterface;

class HttpRendererMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $renderer = $container->get("HtmlRenderer");
        return new HttpRendererMiddleware($renderer);
    }
}
