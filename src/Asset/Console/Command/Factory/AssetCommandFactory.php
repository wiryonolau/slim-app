<?php

namespace App\Asset\Console\Command\Factory;

use Psr\Container\ContainerInterface;
use App\Asset\Console\Command\AssetCommand;
use App\Asset\AssetManager;


class AssetCommandFactory {
    public function __invoke(ContainerInterface $container) {
        $assetManager = $container->get(AssetManager::class);
        return new AssetCommand($assetManager);
    }
}
