<?php
declare(strict_types = 1);

namespace Itseasy\Asset\Factory;

use Itseasy\Asset\AssetManager;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Storage\Adapter\FileSystem;
use Laminas\Cache\StorageFactory;
use Psr\Container\ContainerInterface;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

class AssetManagerFactory
{
    public function __invoke(ContainerInterface $container) : AssetManager
    {
        $asset = $container->get("Config")->getConfig()["asset"];
        $paths = empty($asset["resolver_configs"]["paths"]) ? [] : $asset["resolver_configs"]["paths"];

        $namespace = $asset["caching"]["namespace"];
        $ttl = $asset["caching"]["ttl"];
        $path = $asset["caching"]["path"];

        if (empty($asset["caching"]["class"])) {
            $storage = StorageFactory::factory([
                'adapter' => [
                    'name' => 'filesystem',
                    'options' => [
                        'namespace' => $namespace,
                        'ttl' => $ttl
                    ]
                ],
                'plugins' => [
                    'serializer'
                ]
            ]);
            $cache = new SimpleCacheDecorator($storage);
        } else {
            $cache = $container->get($asset["caching"]["class"]);
        }

        $scssCompiler = new Compiler();
        $scssCompiler->setImportPaths($paths);
        $scssCompiler->setOutputStyle(OutputStyle::COMPRESSED);

        return new AssetManager($paths, $scssCompiler, $cache);
    }
}
