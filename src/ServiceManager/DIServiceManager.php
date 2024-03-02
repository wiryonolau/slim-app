<?php

namespace Itseasy\ServiceManager;

use DI\Container;
use DI\ContainerBuilder;
use Di\Definition\Helper\DefinitionHelper;
use DI\NotFoundException;
use Exception;
use Itseasy\Action\AbstractAction;
use Itseasy\Config;
use Itseasy\Identity\IdentityAwareInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\SharedEventManager;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Log\Logger;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

/**
 * For PHP-DI Container object are created first, then inject service.
 */
class DIServiceManager extends Container implements ServiceManagerInterface
{
    protected $config;
    protected $eventManager;
    protected $translator;
    protected $identityProvider;
    protected $abstractFactories;
    protected $logger;
    protected $view;
    protected $listeners = [];
    protected $allowOverride = true;

    public static function factory(
        Config $config,
        ?LoggerInterface $logger = null,
        ?EventManagerInterface $em = null,
        ?TranslatorInterface $translator = null
    ): ContainerInterface {
        if (is_null($logger)) {
            $logger = new Logger();
        }

        if (is_null($em)) {
            $em = new EventManager(new SharedEventManager());
        }

        if (is_null($translator)) {
            $translator = new Translator();
        }

        // Doesn't support createCompiler in this scenario
        $containerBuilder = new ContainerBuilder(self::class);
        $container = $containerBuilder->build();

        $container->setConfig($config);
        $container->setLogger($logger);
        $container->setEventManager($em);
        $container->setTranslator($translator);

        $container->init();

        // Listener can only be add to EventManager after container done
        foreach ($container->getListeners() as $listener) {
            $listener = $container->get($listener);
            $listener->attach($em);
        }

        return $container;
    }

    /**
     * Indicate whether or not the instance is immutable.
     *
     * @param bool $flag
     */
    public function setAllowOverride(bool $flag)
    {
        $this->allowOverride = $flag;
    }

    /**
     * Retrieve the flag indicating immutability status.
     *
     * @return bool
     */
    public function getAllowOverride()
    {
        return $this->allowOverride;
    }

    public function get($name)
    {
        try {
            return parent::get($name);
        } catch (NotFoundException $e) {
            // Try resolve in abstract factories once
            $this->resolveAbstractFactories($name);

            return parent::get($name);
        }
    }

    public function set($name, $factory)
    {
        if ($this->has($name) && !$this->allowOverride) {
            throw new Exception(sprintf(
                'The container does not allow replacing or updating a service'
                    . ' with existing instances; the following service'
                    . ' already exists in the container: %s',
                $name
            ));
        }
        parent::set($name, $factory);
    }

    public function setService($name, $factory)
    {
        $this->set($name, $factory);
    }

    public function getListeners(): array
    {
        return $this->listeners;
    }

    private function resolveAbstractFactories(string $name)
    {
        foreach ($this->abstractFactories as $factory) {
            $this->registerFactory($name, $factory, [
                'setObjectLogger',
                'setObjectEventManager',
            ]);
            break;
        }
    }

    public function setConfig(Config $config): void
    {
        $this->config = $config;
        $this->set('Config', $this->config);
        $this->set('config', $this->config);
    }

    public function setLogger(?LoggerInterface $logger = null): void
    {
        $this->logger = $logger;
        $this->set('Logger', $this->logger);
        $this->set('logger', $this->logger);
    }

    public function setEventManager(?EventManagerInterface $em = null): void
    {
        $this->eventManager = $em;
        $this->set('EventManager', $this->eventManager);
        $this->set('eventmanager', $this->eventManager);
    }

    public function setTranslator(?TranslatorInterface $translator = null): void
    {
        $this->translator = $translator;
        $this->set('Translator', $this->translator);
        $this->set('translator', $this->translator);
    }

    public function init(): void
    {
        // Identity not initiate during build, retrieve the class name only
        $identityProvider = $this->config->get('guard');
        if (!empty($identityProvider['identity_provider'])) {
            $this->identityProvider = $identityProvider['identity_provider'];
        }

        $this->registerAbstractFactories();
        $this->registerAliases();
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

    private function registerAliases(): void
    {
        $service = $this->config->get('service', []);
        $aliases = (empty($service['aliases']) ? [] : $service['aliases']);

        foreach ($aliases as $alias => $factory) {
            $this->set($alias, \DI\get($factory));
        }
    }

    private function registerAbstractFactories(): void
    {
        $service = $this->config->get('service', []);
        $factories = (empty($service['abstract_factories']) ? [] : $service['abstract_factories']);

        foreach ($factories as $key => $factory) {
            if (is_string($factory) && class_exists($factory)) {
                // Initiate abstract factories class here
                $this->abstractFactories[md5($factory)] = $factory;
            }

            if (!$factory instanceof AbstractFactoryInterface) {
                continue;
            }

            $this->abstractFactories[spl_object_hash($factory)] = $factory;
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
                'setObjectTranslator',
                'setObjectEventManager',
                'setObjectIdentityProvider',
            ]);
        }
    }

    private function registerFactory(
        string $name,
        $factory,
        array $dependencies = []
    ): void {
        // Check if obj is listener without construct, added to EventManager on later process
        if (is_subclass_of($name, ListenerAggregateInterface::class, true)) {
            $this->listeners[] = $name;
        }

        $factory = function (ContainerInterface $container, $requestedName, ?array $options = null) use ($name, $factory, $dependencies) {
            try {
                if ($factory instanceof DefinitionHelper) {
                    $obj = new $name();
                } else {
                    $obj = new $factory();

                    // PHP allow to pass argument more then what is required
                    // PHP-DI only require ContainerInterface
                    // Laminas library require ContainreInterface and requestedName
                    // requestedName will always be DI\Definition\FactoryDefinition use name instead
                    $obj = $obj($container, $name, $options);
                }

                // Register listener
                if ($obj instanceof ListenerAggregateInterface) {
                    $obj->attach($container->get('EventManager'));
                }
            } catch (Exception $ex) {
                debug($ex->getMessage());
                throw new NotFoundException("Factory No entry or class found for '$name'");
            }

            foreach ($dependencies as $dependency) {
                $obj = call_user_func_array([$this, $dependency], [$obj]);
            }

            return $obj;
        };

        $this->set($name, $factory);
    }

    private function setObjectView($obj)
    {
        try {
            if ($obj instanceof AbstractAction) {
                $view_config = $this->config->get('view');
                $viewClass = $view_config['class'];
                $rendererClass = $view_config['renderer'];
                $default_layout = $view_config['default_layout'];

                // View require to be a unique instance for each action
                $view = new $viewClass();
                $view->setRenderer($this->get($rendererClass));
                $view->setLayout($default_layout);
                $obj->setView($view);
            }
        } catch (Exception $e) {
            $this->get('Logger')->debug($e->getMessage());
        }

        return $obj;
    }

    private function setObjectTranslator($obj)
    {
        try {
            if (
                $obj instanceof TranslatorAwareInterface
                and $obj instanceof AbstractAction
            ) {
                $obj->setTranslator($this->translator);
            }
        } catch (Exception $e) {
            $this->get('Logger')->debug($e->getMessage());
        }

        return $obj;
    }

    private function setObjectLogger($obj)
    {
        try {
            if ($obj instanceof LoggerAwareInterface) {
                $obj->setLogger($this->get('Logger'));
            }
        } catch (Exception $e) {
            $this->get('Logger')->debug($e->getMessage());
        }

        return $obj;
    }

    private function setObjectIdentityProvider($obj)
    {
        // Not applicable for service factories due to circular dependency
        // For Action only
        try {
            if (
                $obj instanceof IdentityAwareInterface
                and $obj instanceof AbstractAction
                and $this->has($this->identityProvider)
            ) {
                $obj->setIdentityProvider($this->get($this->identityProvider));
            }
        } catch (Exception $e) {
            $this->get('Logger')->debug($e->getMessage());
        }

        return $obj;
    }

    private function setObjectEventManager($obj)
    {
        try {
            if ($obj instanceof EventManagerAwareInterface) {
                $obj->setEventManager($this->get('EventManager'));
            }
        } catch (Exception $e) {
            $this->get('Logger')->debug($e->getMessage());
        }

        return $obj;
    }
}
