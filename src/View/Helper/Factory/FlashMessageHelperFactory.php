<?php
declare(strict_types = 1);

namespace Itseasy\View\Helper\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\View\Helper\FlashMessageHelper;

class FlashMessageHelperFactory
{
    public function __invoke(ContainerInterface $container) : FlashMessageHelper
    {
        $config = $container->get("Config")->getConfig();
        $session = $container->get($config["session"]["class"]);
        return new FlashMessageHelper($session);
    }
}
