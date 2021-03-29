<?php

namespace Itseasy\Asset;

use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class AssetManager {
    protected $paths = [];
    protected $cache;

    public function __construct(array $paths = [], CacheAdapterInterface $cache) {
        $this->paths = array_map("realpath", $paths);
        $this->cache = $cache;
    }

    public function build() {
        $assets = [];

        foreach($this->paths as $path) {
            $dir = new RecursiveDirectoryIterator($path);
            $iter = new RecursiveIteratorIterator($dir);
            $cssFiles = new RegexIterator($iter, '/.*(.css)$/', RegexIterator::GET_MATCH);
            $jsFiles = new RegexIterator($iter, '/.*(.js)$/', RegexIterator::GET_MATCH);
            foreach($cssFiles as $file) {
                $assets = array_merge($assets, [$file[0]]);
            }
            foreach($jsFiles as $file) {
                $assets = array_merge($assets, [$file[0]]);

            }
        }

        foreach ($assets as $file) {
            $name = $this->hashName($file);
            $asset = $this->cache->getItem($name);
            $this->setAsset($asset, $file);
        }
    }

    public function clear() {
        $this->cache->prune();
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
            $real_path = sprintf("%s%s", $path, $file_path);
            if (realpath($real_path) !== false) {
                return $real_path;
            }
        }
        return null;
    }

    protected function setAsset(ItemInterface &$asset, string $file_path) : void {
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        $content = file_get_contents($file_path);

        switch ($extension) {
            case "js":
                $pattern = "/.*(.min.js$)/";
            break;
            case "css":
                $pattern = "/.*(.min.css$)/";
            break;
            default:
                $pattern = null;
        }

        if (!is_null($pattern) and preg_match($pattern, pathinfo($file_path, PATHINFO_BASENAME)) !== 1) {
            $content = $this->minify($extension, $content);
        }

        $name = $this->hashName($file_path);
        $asset->set($content);
        $this->cache->save($asset);
    }

    protected function minify(string $extension, string $content) : string {
        switch ($extension) {
            case "js":
                return Filter\JSMin::minify($content);
            break;
            case "css":
                return Filter\CssMin::minify($content);
            break;
            default:
                return $content;
        }
    }

    protected function hashName(string $file_path) : string {
        return md5($file_path);
    }
}
