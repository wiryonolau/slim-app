<?php

namespace Itseasy\Asset\Factory;

use Itseasy\Asset\AssetManager;
use Itseasy\Asset\AssetMiddleware;
use Psr\Container\ContainerInterface;

class AssetMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $assetManager = $container->get(AssetManager::class);
        return new AssetMiddleware($assetManager);
    }
}
