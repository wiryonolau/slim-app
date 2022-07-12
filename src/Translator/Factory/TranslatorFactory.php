<?php

namespace Itseasy\Translator\Factory;

use Itseasy\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Psr\Container\ContainerInterface;

class TranslatorFactory
{
    public function __invoke(ContainerInterface $container): TranslatorInterface
    {
        $config = $container->get('Config')->get('translator', []);

        return Translator::factory($config);
    }
}
