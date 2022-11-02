<?php

declare(strict_types=1);

namespace Itseasy;

use ArrayAccess;
use Exception;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;
use Laminas\Stdlib\ArrayUtils;

class Config implements ArrayAccess
{
    protected $config = [];

    public function __construct($config_dirs, $config_array = [])
    {
        $this->parseConfig($config_dirs);
        $this->config = ArrayUtils::merge($this->config, $config_array, false);
    }

    public function merge(
        array $config,
        bool $preserveNumericKeys = false,
        string $key = "app"
    ): void {
        if (empty($this->config[$key])) {
            $this->config[$key] = [];
        }
        $this->config[$key] = ArrayUtils::merge(
            $this->config[$key],
            $config,
            $preserveNumericKeys
        );
    }

    /**
     * @return mixed|null
     */
    public function get($key, $placeholder = null)
    {
        if (empty($this->config[$key])) {
            return $placeholder;
        }

        return $this->config[$key];
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    private function parseConfig($config_dirs): void
    {
        $providers = [];
        foreach ($config_dirs as $config_dir) {
            $providers[] = new PhpFileProvider($config_dir);
        }

        $aggregator = new ConfigAggregator($providers);
        $this->config = $aggregator->getMergedConfig();
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception('Cannot set config programmatically');
    }

    public function offsetUnset($offset)
    {
        throw new Exception('Cannot set config programmatically');
    }
}
