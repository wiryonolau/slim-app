<?php

namespace App;

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
        foreach ($config_dirs as $config_dir) {
            foreach (glob(sprintf("%s/*.config.php", $config_dir)) as $file) {
                $this->config = array_merge_recursive($this->config, include $file);
            }
        }
    }
}
