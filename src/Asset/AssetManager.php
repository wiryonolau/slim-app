<?php
declare(strict_types = 1);

namespace Itseasy\Asset;

use Psr\SimpleCache\CacheInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Exception;

class AssetManager
{
    protected $paths = [];
    protected $cache;

    public function __construct(array $paths = [], CacheInterface $cache)
    {
        $this->paths = array_map("realpath", $paths);
        $this->cache = $cache;
    }

    public function build() : void
    {
        $assets = [];
        foreach ($this->paths as $path) {
            $dir = new RecursiveDirectoryIterator($path);
            $iter = new RecursiveIteratorIterator($dir);
            $cssFiles = new RegexIterator($iter, '/.*(.css)$/', RegexIterator::GET_MATCH);
            $jsFiles = new RegexIterator($iter, '/.*(.js)$/', RegexIterator::GET_MATCH);
            foreach ($cssFiles as $file) {
                $assets = array_merge($assets, [$file[0]]);
            }
            foreach ($jsFiles as $file) {
                $assets = array_merge($assets, [$file[0]]);
            }
        }

        foreach ($assets as $file) {
            $name = $this->hashName($file);
            $this->setAsset($name, $file);
        }
    }

    public function clear() : void
    {
        $this->cache->clear();
    }

    public function getAsset(string $file_path) : ?string
    {
        $name = $this->hashName($file_path);
        if (!$this->cache->has($name)) {
            $this->setAsset($name, $file_path);
        }
        return $this->cache->get($name);
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

    protected function setAsset(string $name, string $file_path) : void
    {
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
        $this->cache->set($name, $content);
    }

    protected function minify(string $extension, string $content) : string
    {
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

    protected function hashName(string $file_path) : string
    {
        return md5($file_path);
    }
}
