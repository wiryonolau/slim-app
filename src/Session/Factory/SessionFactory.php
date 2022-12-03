<?php

declare(strict_types=1);

namespace Itseasy\Session\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

class SessionFactory
{
    public function __invoke(ContainerInterface $container): Session
    {
        $options = $container->get('Config')->getConfig()['session']['options'];

        if (PHP_SAPI === 'cli') {
            $storage = new MockArraySessionStorage();
        } else {
            $storage = new NativeSessionStorage($options, new NativeFileSessionHandler());
        }

        return new Session($storage);
    }
}
