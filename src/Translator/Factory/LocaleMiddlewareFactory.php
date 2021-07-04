<?php
declare(strict_types = 1);

namespace Itseasy\Translator\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Translator\LocaleMiddleware;
use Itseasy\Translator\Translator;

class LocaleMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : LocaleMiddleware
    {
        $translator = $container->get(Translator::class);
        return new LocaleMiddleware($translator);
    }
}
