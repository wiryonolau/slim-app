<?php

declare(strict_types=1);

namespace Itseasy\Asset\Console\Command\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Asset\Console\Command\AssetCommand;
use Itseasy\Asset\AssetManager;

class AssetCommandFactory
{
    public function __invoke(ContainerInterface $container): AssetCommand
    {
        $assetManager = $container->get(AssetManager::class);
        return new AssetCommand($assetManager);
    }
}
