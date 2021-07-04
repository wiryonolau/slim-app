<?php

namespace Itseasy\Translator\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;

class TranslatorFactory
{
    public function __invoke(ContainerInterface $container) : TranslatorInterface
    {
        $config = $container->get("Config")->get("translator", []);
        return Translator::factory($config);
    }
}
