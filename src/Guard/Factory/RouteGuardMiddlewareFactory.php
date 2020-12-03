<?php

namespace App\Guard\Factory;

use App\Guard\RouteGuard;
use App\Guard\RouteGuardMiddleware;
use Psr\Container\ContainerInterface;

class RouteGuardMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $routeGuard = $container->get(RouteGuard::class);
        return new RouteGuardMiddleware($routeGuard);
    }
}
