<?php

namespace App\Asset;

use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;

class AssetManager {
    protected $paths = [];
    protected $cache;

    public function __construct(array $paths = [], CacheAdapterInterface $cache) {
        // TODO: Implement cache
        $this->paths = array_map("realpath", $paths);
        $this->cache = $cache;
    }

    public function getAsset(string $file_path) : ?string {
        $name = $this->hashName($file_path);
        $asset = $this->cache->getItem($name);

        if (!$asset->isHit()) {
            $this->addAsset($file_path);
        }
        return $asset->get();
    }

    public function setAsset(string $file_path) : void {
        $name = $this->hashName($file_path);
        $asset = $this->cache->getItem($name);
        $asset->set(file_get_contents($file_path));
        $cache->save($asset);
    }

    public function getAssetRealPath(string $file_path) : ?string
    {
        foreach ($this->paths as $path) {
            $file_path = sprintf("%s%s", $path, $file);
            if (realpath($file_path)) {
                return $file_path;
            }
        }
        return null;
    }

    protected function hashName(string $file_path) : string {
        return md5($file_path);
    }
}
