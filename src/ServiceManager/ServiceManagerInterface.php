<?php

namespace Itseasy\ServiceManager;

use Itseasy\Config;
use Laminas\EventManager\EventManagerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

interface ServiceManagerInterface
{
    public static function factory(
        Config $config,
        ?LoggerInterface $logger = null,
        ?EventManagerInterface $em = null,
        ?TranslatorInterface $translator = null
    ): ContainerInterface;
}
