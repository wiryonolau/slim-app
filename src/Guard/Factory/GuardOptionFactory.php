<?php

namespace App\Guard\Factory;

use App\Guard\GuardOption;
use Psr\Container\ContainerInterface;

class GuardOptionFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config")->getConfig();
        return new GuardOption($config["guard"]);
    }
}
