<?php

namespace App\Middleware\Factory;

use App\Middleware\AssetMiddleware;
use Psr\Container\ContainerInterface;

class AssetMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $asset = $container->get("Config")->getConfig()["asset"];
        return new AssetMiddleware($asset);
    }
}
