<?php

namespace Itseasy\Asset\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Asset\AssetManager;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class AssetManagerFactory {
    public function __invoke(ContainerInterface $container) {
        $asset = $container->get("Config")->getConfig()["asset"];
        $paths = empty($asset["resolver_configs"]["paths"]) ? [] : $asset["resolver_configs"]["paths"];

        $namespace = $asset["caching"]["namespace"];
        $ttl = $asset["caching"]["ttl"];
        $path = $asset["caching"]["path"];

        if (empty($asset["caching"]["class"])) {
            $cache = new FilesystemAdapter($namespace, $ttl, $path);
        } else {
            $cache = $container->get($asset["caching"]["class"]);
        }
        return new AssetManager($paths, $cache);
    }
}
