<?php

namespace Itseasy\ServiceManager;

use Exception;
use Itseasy\Action\AbstractAction;
use Itseasy\Config;
use Itseasy\Identity\IdentityAwareInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Log\Logger;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\SharedEventManager;

/**
 * For Laminas service is configured first then created the container.
 */
class LaminasServiceManager implements ServiceManagerInterface
{
    protected $config;
    protected $factories = [];
    protected $listeners = [];

    public static function factory(
        Config $config,
        ?LoggerInterface $logger = null,
        ?EventManagerInterface $em = null
    ): ContainerInterface {
        if (is_null($logger)) {
            $logger = new Logger();
        }

        if (is_null($em)) {
            $em = new EventManager(new SharedEventManager());
        }

        $service = new LaminasServiceManager($config);
        $service->build();

        $serviceConfig = $config->getConfig()['service'];
        $serviceConfig['factories'] = $service->getFactories();

        $container = new ServiceManager($serviceConfig);

        self::setConfig($container, $config);
        self::setLogger($container, $logger);
        self::setEventManager($container, $em);

        // Listener can only be add to EventManager after container done
        foreach ($service->getListeners() as $listener) {
            $listener = $container->get($listener);
            $listener->attach($em);
        }

        return $container;
    }

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getFactories(): array
    {
        return $this->factories;
    }

    public function getListeners(): array
    {
        return $this->listeners;
    }

    private static function setConfig(
        ContainerInterface &$container,
        Config $config
    ): void {
        $container->setService('Config', $config);
        $container->setService('config', $config);
    }

    private static function setLogger(
        ContainerInterface &$container,
        LoggerInterface $logger
    ): void {
        $container->setService('Logger', $logger);
    }

    private static function setEventManager(
        ContainerInterface &$container,
        EventManagerInterface $em
    ): void {
        $container->setService('EventManager', $em);
    }

    public function build(): void
    {
        // Identity not initiate during build, retrieve the class name only
        $identityProvider = $this->config->get('guard');
        if (!empty($identityProvider['identity_provider'])) {
            $this->identityProvider = $identityProvider['identity_provider'];
        }

        $this->registerService();
        $this->registerCommand();
        $this->registerViewHelper();
        $this->registerHttpAction();
    }

    private function registerService(): void
    {
        $service = $this->config->get('service', []);
        $factories = (empty($service['factories']) ? [] : $service['factories']);

        foreach ($factories as $name => $factory) {
            $this->registerFactory($name, $factory, [
                'setObjectLogger',
                'setObjectEventManager',
            ]);
        }
    }

    private function registerViewHelper(): void
    {
        $view_helpers = $this->config->get('view_helpers', []);
        $factories = (empty($view_helpers['factories']) ? [] : $view_helpers['factories']);

        foreach ($factories as $name => $factory) {
            $this->registerFactory($name, $factory, [
                'setObjectLogger',
                'setObjectEventManager',
            ]);
        }
    }

    private function registerCommand(): void
    {
        $console = $this->config->get('console', []);
        $factories = (empty($console['factories']) ? [] : $console['factories']);

        foreach ($factories as $name => $factory) {
            $this->registerFactory($name, $factory, [
                'setObjectLogger',
                'setObjectEventManager',
            ]);
        }
    }

    private function registerHttpAction(): void
    {
        $action = $this->config->get('action', []);
        $factories = (empty($action['factories']) ? [] : $action['factories']);

        foreach ($factories as $name => $factory) {
            $this->registerFactory($name, $factory, [
                'setObjectView',
                'setObjectLogger',
                'setObjectEventManager',
                'setObjectIdentityProvider',
            ]);
        }
    }

    /**
     * Wrap actualFactory with another layer for injection
     */
    private function registerFactory(
        string $name,
        $actualFactory,
        array $dependencies = []
    ): void {
        // Check if obj is listener without construct, added to EventManager on later process
        if (is_subclass_of($name, ListenerAggregateInterface::class, true)) {
            $this->listeners[] = $name;
        }

        $factory = function (
            ContainerInterface $container,
            $requestedName,
            ?array $options = null
        ) use (
            $name,
            $actualFactory,
            $dependencies
        ) {
            try {
                $obj = new $actualFactory();
                // requestedName always equal name
                $obj = $obj($container, $requestedName, $options);
            } catch (Exception $ex) {
                debug($ex->getMessage());
                throw new Exception("Factory No entry or class found for '$name'");
            }

            foreach ($dependencies as $dependency) {
                $obj = call_user_func_array([$this, $dependency], [$obj, $container]);
            }

            return $obj;
        };

        $this->factories[$name] = $factory;
    }

    private function setObjectView($obj, ContainerInterface $container)
    {
        try {
            if ($obj instanceof AbstractAction) {
                $view_config = $this->config->get('view');
                $viewClass = $view_config['class'];
                $rendererClass = $view_config['renderer'];
                $default_layout = $view_config['default_layout'];

                // View require to be a unique instance for each action
                $view = new $viewClass();
                $view->setRenderer($container->get($rendererClass));
                $view->setLayout($default_layout);
                $obj->setView($view);
            }
        } catch (Exception $e) {
            $container->get('Logger')->debug($e->getMessage());
        }

        return $obj;
    }

    private function setObjectLogger($obj, ContainerInterface $container)
    {
        try {
            if ($obj instanceof LoggerAwareInterface) {
                $obj->setLogger($container->get('Logger'));
            }
        } catch (Exception $e) {
            $container->get('Logger')->debug($e->getMessage());
        }

        return $obj;
    }

    private function setObjectIdentityProvider($obj, ContainerInterface $container)
    {
        // Not applicable for service factories due to circular dependency
        // For Action only
        try {
            if (
                $obj instanceof IdentityAwareInterface
                and $obj instanceof AbstractAction
                and $container->has($this->identityProvider)
            ) {
                $obj->setIdentityProvider($container->get($this->identityProvider));
            }
        } catch (Exception $e) {
            $container->get('Logger')->debug($e->getMessage());
        }

        return $obj;
    }

    private function setObjectEventManager($obj, ContainerInterface $container)
    {
        try {
            if ($obj instanceof EventManagerAwareInterface) {
                $obj->setEventManager($container->get('EventManager'));
            }
        } catch (Exception $e) {
            $container->get('Logger')->debug($e->getMessage());
        }

        return $obj;
    }
}
