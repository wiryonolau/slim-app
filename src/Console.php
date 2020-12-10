<?php

namespace App;

use DI;
use Symfony\Component\Console\Application;

class Console {
    protected $config;
    protected $container;
    protected $application;

    public function __construct($config_dirs = null, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        if (!is_array($config_dirs)) {
            $config_dirs = [$config_dirs];
        }
        array_unshift($config_dirs, __DIR__."/../config/*.config.php");

        $this->config = new Config($config_dirs);
        $this->container = new DI\Container();
        $this->application = new Application($name, $version);

        $this->buildContainer();
        $this->setCommand();
    }

    public function getConfig() {
        return $this->config->getConfig();
    }

    public function getContainer() {
        return $this->container;
    }

    public function getApplication() {
        return $this->application;
    }

    public function setCommand() {
        $commands = [];
        if (!empty($this->getConfig()["console"]["commands"])) {
            foreach ($this->getConfig()["console"]["commands"] as $command) {
                $commands[] = $this->container->get($command);
            }
        }
        $this->application->addCommands($commands);
    }

    public function buildContainer() {
        $this->addDefinition('Config', $this->config);

        # Build Service
        if (!empty($this->getConfig()["service"]["factories"])) {
            foreach ($this->getConfig()["service"]["factories"] as $service => $factory) {
                $this->addDefinition($service, $factory);
            }
        }

        if (!empty($this->getConfig()["console"]["factories"])) {
            foreach ($this->getConfig()["console"]["factories"] as $console => $factory) {
                $this->addDefinition($console, $factory);
            }
        }
    }

    public function run() {
        $this->application->run();
    }

    private function addDefinition($name, $class)
    {
        if (is_object($class)) {
            $this->container->set($name, $class);
        } else {
            $this->container->set($name, DI\factory($class));
        }
    }
}
