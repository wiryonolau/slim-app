<?php

namespace Itseasy\Navigation\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Navigation\Navigation;

class NavigationFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config")->getConfig();

        if (empty($config["navigation"])) {
            $navigation_config = ["default" => []];
        } else {
            $navigation_config = $config["navigation"];
        }

        return new Navigation($navigation_config);
    }
}
