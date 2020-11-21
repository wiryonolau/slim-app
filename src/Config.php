<?php

namespace App;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

class Config
{
    protected $config = [];

    public function __construct($config_dirs)
    {
        $this->parseConfig($config_dirs);
    }

    public function getConfig()
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
        debug($this->config);
    }
}
