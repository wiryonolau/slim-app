<?php

namespace App\Asset\Factory;

use App\Asset\AssetManager;
use App\Asset\AssetMiddleware;
use Psr\Container\ContainerInterface;

class AssetMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $assetManager = $container->get(AssetManager::class);
        return new AssetMiddleware($assetManager);
    }
}
