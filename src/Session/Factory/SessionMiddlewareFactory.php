<?php

namespace Itseasy\Session\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Session\SessionMiddleware;
use Itseasy\Session;

class SessionMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $sessionClass = $container->get("Config")->getConfig()["session"]["class"];
        $session = $container->get($sessionClass);
        return new SessionMiddleware($session);
    }
}
