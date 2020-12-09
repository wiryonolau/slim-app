<?php

namespace App\View\Helper\Factory;

use Psr\Container\ContainerInterface;
use App\View\Helper\FlashMessageHelper;

class FlashMessageHelperFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config")->getConfig();
        $session = $container->get($config["session"]["class"]);
        return new FlashMessageHelper($session);
    }
}
