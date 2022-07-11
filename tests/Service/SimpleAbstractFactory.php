<?php

namespace Itseasy\Test\Service;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

class SimpleAbstractFactory implements AbstractFactoryInterface
{
    protected $config;

    protected $configKey = 'test';

    public function canCreate($container, $requestedName)
    {
        $config = $this->getConfig($container);
        if (empty($config)) {
            return false;
        }

        return isset($config[$requestedName]) && is_array($config[$requestedName]);
    }

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $this->getConfig($container);

        return new TestService($requestedName, $config[$requestedName]);
    }

    protected function getConfig(ContainerInterface $container)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$container->has('config')) {
            $this->config = [];

            return $this->config;
        }

        $config = $container->get('config');
        if (!isset($config[$this->configKey])) {
            $this->config = [];

            return $this->config;
        }

        $cacheConfigurations = $config[$this->configKey];

        return $this->config = $cacheConfigurations;
    }
}
