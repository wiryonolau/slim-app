<?php

namespace Itseasy;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

class Config
{
    protected $config = [];

    public function __construct($config_dirs)
    {
        $this->parseConfig($config_dirs);
    }

    public function get($key, $placeholder = null) {
        if (empty($this->config[$key])) {
            return $placeholder;
        }
        return $this->config[$key];
    }

    public function getConfig() : array
    {
        return $this->config;
    }

    private function parseConfig($config_dirs)
    {
        $providers = [];
        foreach ($config_dirs as $config_dir) {
            $providers[] = new PhpFileProvider($config_dir);
        }

        $aggregator = new ConfigAggregator($providers);
        $this->config = $aggregator->getMergedConfig();
    }
}
