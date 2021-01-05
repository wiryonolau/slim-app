<?php

namespace Itseasy\Navigation\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Navigation\NavigationMiddleware;
use Itseasy\Navigation\Navigation;

class NavigationMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $navigation = $container->get(Navigation::class);
        return new NavigationMiddleware($navigation);
    }
}
