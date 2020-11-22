<?php

namespace App\Middleware\Factory;

use Psr\Container\ContainerInterface;
use App\Middleware\SessionMiddleware;
use App\Session;

class SessionMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $session = $container->get(Session::class);
        return new SessionMiddleware($session);
    }
}
