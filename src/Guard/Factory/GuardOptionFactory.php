<?php

namespace Itseasy\Guard\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Guard\GuardOption;

class GuardOptionFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config")->getConfig();
        return new GuardOption($config["guard"]);
    }
}
