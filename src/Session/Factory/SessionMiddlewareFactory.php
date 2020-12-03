<?php

namespace App\Session\Factory;

use Psr\Container\ContainerInterface;
use App\Session\SessionMiddleware;
use App\Session;

class SessionMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $session = $container->get(Session::class);
        return new SessionMiddleware($session);
    }
}
