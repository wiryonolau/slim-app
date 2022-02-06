<?php

namespace Itseasy;

use DI\Container;
use DI\ContainerBuilder;
use Di\Definition\Helper\DefinitionHelper;
use Itseasy\Action\AbstractAction;
use Itseasy\Identity\IdentityAwareInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Log\Logger;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerInterface;

use function DI\factory;

class ServiceManager extends Container
{
    protected $config;
    protected $eventManager;
    protected $identityProvider;
    protected $logger;
    protected $view;

    public static function factory(
        Config $config,
        ?LoggerInterface $logger = null,
        ?EventManagerInterface $em = null,
        ?string $cache_path = null
    ) : Container {
        $containerBuilder = new ContainerBuilder(self::class);
        if (!is_null($cache_path)) {
            $containerBuilder->enableCompiliation($cache_path);
        }
        $container = $containerBuilder->build();

        $container->setConfig($config);
        $container->setLogger($logger);
        $container->setEventManager($em);

        $container->build();

        return $container;
    }

    public function setConfig(Config $config) : void
    {
        $this->config = $config;
        $this->set('Config', $this->config);
        $this->set('config', $this->config);
    }

    public function setLogger(?LoggerInterface $logger = null) : void
    {
        if (is_null($logger)) {
            $logger = new Logger();
        }

        $this->logger = $logger;
        $this->set('Logger', $this->logger);
        $this->set('logger', $this->logger);
    }

    public function setEventManager(?EventManagerInterface $em = null) : void
    {
        if (is_null($em)) {
            $em = new EventManager();
        }

        $this->eventManager = $em;
        $this->set('EventManager', $this->eventManager);
        $this->set('eventmanager', $this->eventManager);
    }

    public function build() : void
    {
        // Identity not initiate during build, retrieve the class name only
        $identityProvider = $this->config->get("guard");
        if (!empty($identityProvider["identity_provider"])) {
            $this->identityProvider = $identityProvider["identity_provider"];
        }

        $this->registerService();
        $this->registerCommand();
        $this->registerHttpAction();
    }

    private function registerService() : void
    {
        $service = $this->config->get("service", []);
        $factories = (empty($service["factories"]) ? [] : $service["factories"]);

        foreach ($factories as $name => $factory) {
            $this->registerFactory($name, $factory, [
                "setObjectLogger",
                "setObjectEventManager"
            ]);
        }
    }

    private function registerCommand() : void
    {
        $console = $this->config->get("console", []);
        $factories = (empty($console["factories"]) ? [] : $console["factories"]);

        foreach ($factories as $name => $factory) {
            $this->registerFactory($name, $factory, [
                "setObjectLogger",
                "setObjectEventManager"
            ]);
        }
    }

    private function registerHttpAction() : void
    {
        $action = $this->config->get("action", []);
        $factories = (empty($action["factories"]) ? [] : $action["factories"]);

        foreach ($factories as $name => $factory) {
            $this->registerFactory($name, $factory, [
                "setObjectView",
                "setObjectLogger",
                "setObjectEventManager",
                "setObjectIdentityProvider"
            ]);
        }
    }

    private function registerFactory(
        string $name,
        $factory,
        array $dependencies = []
    ) : void {
        if (is_object($factory)) {
            $this->set($name, $factory);
        } else {
            $factory = function () use ($name, $factory, $dependencies) {
                if ($factory instanceof DefinitionHelper) {
                    $obj = new $name;
                } else {
                    $obj = new $factory();
                    // Invoke class
                    $obj = $obj($this);
                }

                foreach ($dependencies as $dependency) {
                    $obj = call_user_func_array([$this, $dependency], [$obj]);
                }

                return $obj;
            };
            $this->set($name, \DI\factory($factory));
        }
    }

    private function setObjectView($obj)
    {
        if ($obj instanceof AbstractAction) {
            $view_config = $this->config->get("view");
            $viewClass = $view_config["class"];
            $rendererClass = $view_config["renderer"];
            $default_layout = $view_config["default_layout"];

            // View require to be a unique instance for each action
            $view = new $viewClass();
            $view->setRenderer($this->get($rendererClass));
            $view->setLayout($default_layout);
            $obj->setView($view);
        }
        return $obj;
    }

    private function setObjectLogger($obj)
    {
        if ($obj instanceof LoggerAwareInterface) {
            $obj->setLogger($this->get("Logger"));
        }
        return $obj;
    }

    private function setObjectIdentityProvider($obj)
    {
        // Not applicable for service factories due to circular dependency
        // For Action only
        if ($obj instanceof IdentityAwareInterface
            and $obj instanceof AbstractAction
            ) {
            $obj->setIdentityProvider($this->get($this->identityProvider));
        }
        return $obj;
    }

    private function setObjectEventManager($obj)
    {
        if ($obj instanceof EventManagerAwareInterface) {
            $obj->setEventManager($this->get("EventManager"));
        }
        return $obj;
    }
}
