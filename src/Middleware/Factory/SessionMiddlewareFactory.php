<?php

namespace App\Middleware\Factory;

use App\Middleware\SessionMiddleware;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $settings = $container->get('Config')->getConfig()['session'];

        if (PHP_SAPI === 'cli') {
            return new Session(new MockArraySessionStorage());
        } else {
            return new Session(new NativeSessionStorage($settings));
        }

        return new SessionMiddleware($session);
    }
}
