<?php

namespace App\Asset;

use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

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

        if (!$asset->isHit() or $asset->get() == "") {
            $this->setAsset($asset, $file_path);
        }
        return $asset->get();
    }

    public function getAssetRealPath(string $file_path) : ?string
    {
        foreach ($this->paths as $path) {
            $file_path = sprintf("%s%s", $path, $file_path);
            if (realpath($file_path)) {
                return $file_path;
            }
        }
        return null;
    }

    protected function setAsset(ItemInterface &$asset, string $file_path) : void {
        $name = $this->hashName($file_path);
        $asset->set(file_get_contents($file_path));
        $this->cache->save($asset);
    }

    protected function hashName(string $file_path) : string {
        return md5($file_path);
    }
}
