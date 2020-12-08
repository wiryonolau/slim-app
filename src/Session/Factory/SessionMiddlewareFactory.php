<?php

namespace App\Session\Factory;

use Psr\Container\ContainerInterface;
use App\Session\SessionMiddleware;
use App\Session;

class SessionMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $sessionClass = $container->get("Config")->getConfig()["session"]["class"];
        $session = $container->get($sessionClass);
        return new SessionMiddleware($session);
    }
}
