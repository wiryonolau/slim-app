<?php

namespace App\Guard\Factory;

use App\Guard\RouteGuard;
use App\Guard\RouteGuardMiddleware;
use App\View\Helper\UrlHelper;
use Psr\Container\ContainerInterface;

class RouteGuardMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $routeGuard = $container->get(RouteGuard::class);
        $urlHelper  = $container->get(UrlHelper::class);
        return new RouteGuardMiddleware($routeGuard, $urlHelper);
    }
}
