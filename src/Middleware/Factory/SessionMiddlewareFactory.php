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
            return new SessionMiddleware(new MockArraySessionStorage());
        } else {
            return new SessionMiddleware(new NativeSessionStorage($settings));
        }
    }
}
