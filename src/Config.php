<?php

namespace App;

class Config {
    protected $config = [];

    public function __construct($config_dir) {
        $this->parseConfig($config_dir);
    }

    public function getConfig() {
        return $this->config;
    }

    private function parseConfig($config_dir) {
        $config_path = realpath($config_dir);                                                                                                             
        foreach (glob(sprintf("%s/*.config.php", $config_dir)) as $file) {
            $this->config = array_merge_recursive($this->config, include $file);
        }
    }
}
?>
