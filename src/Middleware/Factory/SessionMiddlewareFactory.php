<?php

namespace App\Middleware\Factory;

use Psr\Container\ContainerInterface;
use App\Middleware\SessionMiddleware;

class SessionMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $session = $container->get("Session");
        return new SessionMiddleware($session);
    }
}
