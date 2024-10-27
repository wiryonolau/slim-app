<?php

declare(strict_types=1);

namespace Itseasy;

use ArrayAccess;
use Exception;
use Itseasy\Stdlib\ArrayUtils as ItseasyArrayUtils;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\LaminasConfigProvider;
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
        return ItseasyArrayUtils::query(
            $this->config,
            $key,
            $placeholder,
            true,
            "."
        );
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    private function parseConfig($config_dirs): void
    {
        $providers = [];
        foreach ($config_dirs as $config_dir) {
            $providers[] = new LaminasConfigProvider($config_dir);
        }

        $aggregator = new ConfigAggregator($providers);
        $this->config = $aggregator->getMergedConfig();
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new Exception('Cannot set config programmatically');
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new Exception('Cannot set config programmatically');
    }
}
