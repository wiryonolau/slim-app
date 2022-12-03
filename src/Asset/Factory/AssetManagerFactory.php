<?php

declare(strict_types=1);

namespace Itseasy\Asset\Factory;

use Itseasy\Asset\AssetManager;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Psr\Container\ContainerInterface;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

class AssetManagerFactory
{
    public function __invoke(ContainerInterface $container): AssetManager
    {
        $asset = $container->get('Config')->getConfig()['asset'];
        $paths = empty($asset['resolver_configs']['paths']) ? [] : $asset['resolver_configs']['paths'];

        $namespace = $asset['caching']['namespace'];
        $ttl = $asset['caching']['ttl'];
        $path = $asset['caching']['path'];

        if (empty($asset['caching']['class'])) {
            $storageFactory = $container->get(
                \Laminas\Cache\Service\StorageAdapterFactoryInterface::class
            );

            $storage = $storageFactory->createFromArrayConfiguration([
                'adapter' => 'filesystem',
                'options' => [
                    'namespace' => $namespace,
                    'ttl' => $ttl,
                ],
                'plugins' => [
                    [
                        'name' => 'serializer',
                    ],
                    [
                        'name' => 'exception_handler',
                        'options' => [
                            'throw_exceptions' => false,
                        ],
                    ],
                ],
            ]);

            $cache = new SimpleCacheDecorator($storage);
        } else {
            $cache = $container->get($asset['caching']['class']);
        }

        $scssCompiler = new Compiler();
        $scssCompiler->setImportPaths($paths);
        $scssCompiler->setOutputStyle(OutputStyle::COMPRESSED);

        return new AssetManager($paths, $scssCompiler, $cache);
    }
}
