<?php

namespace App\Asset\Factory;

use Psr\Container\ContainerInterface;
use App\Asset\AssetManager;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class AssetManagerFactory {
    public function __invoke(ContainerInterface $container) {
        $asset = $container->get("Config")->getConfig()["asset"];
        $paths = empty($asset["resolver_configs"]["paths"]) ? [] : $asset["resolver_configs"]["paths"];
        if (empty($asset["caching"]["class"])) {
            $cache = new FilesystemAdapter("asset", 86400);
        } else {
            $cache = $container->get($asset["caching"]["class"]);
        }
        return new AssetManager($paths, $cache);
    }
}
