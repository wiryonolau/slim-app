<?php

declare(strict_types=1);

namespace Itseasy\Asset\Factory;

use Itseasy\Asset\AssetManager;
use Itseasy\Asset\AssetMiddleware;
use Psr\Container\ContainerInterface;

class AssetMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AssetMiddleware
    {
        $assetManager = $container->get(AssetManager::class);
        return new AssetMiddleware($assetManager);
    }
}
