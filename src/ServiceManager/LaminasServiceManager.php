<?php

namespace Itseasy\ServiceManager;

use Itseasy\Action\AbstractAction;
use Itseasy\Config;
use Itseasy\Guard\GuardOption;
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
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorInterface;

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
            $translator = Translator::factory($config->getConfig()["translator"] ?? []);
        }

        $service = new LaminasServiceManager($config);
        $service->build();

        $serviceConfig = $config->getConfig()['service'];
        $serviceConfig['factories'] = $service->getFactories();
        $serviceConfig['initializers'] = [
            // This function is called last when using get / build
            function (ContainerInterface $container, $instance) {
                if ($instance instanceof LoggerAwareInterface) {
                    $instance->setLogger($container->get('Logger'));
                }

                if (
                    $instance instanceof AbstractAction
                ) {
                    $config = $container->get("Config");

                    $view_config = $config->get('view');
                    $viewClass = $view_config['class'];
                    $rendererClass = $view_config['renderer'];
                    $default_layout = $view_config['default_layout'];

                    // View require to be a unique instance for each action
                    $view = new $viewClass();
                    $view->setRenderer($container->get($rendererClass));
                    $view->setLayout($default_layout);
                    $instance->setView($view);

                    if (
                        $instance instanceof IdentityAwareInterface
                        and $container->has(GuardOption::class)
                    ) {
                        $guardOptions = $container->get(GuardOption::class);

                        $instance->setIdentityProvider(
                            $container->get($guardOptions->getIdentityProvider())
                        );
                    }
                }

                if ($instance instanceof EventManagerAwareInterface) {
                    $instance->setEventManager($container->get('EventManager'));
                }

                if ($instance instanceof TranslatorAwareInterface) {
                    $instance->setTranslator($container->get("Translator"));
                }
            }
        ];

        $container = new ServiceManager($serviceConfig);

        // Add config, logger, eventmanager after action and service registered
        self::setConfig($container, $config);
        self::setLogger($container, $logger);
        self::setEventManager($container, $em);
        self::setTranslator($container, $translator);

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

    private static function setTranslator(
        ContainerInterface &$container,
        TranslatorInterface $translator
    ): void {
        $container->setService('Translator', $translator);
    }

    public function build(): void
    {
        $this->registerService();
        $this->registerCommand();
        $this->registerViewHelper();
        $this->registerHttpAction();
    }

    /**
     * Retrieve factory definition from service config
     */
    private function registerService(): void
    {
        $service = $this->config->get('service', []);
        $factories = (empty($service['factories']) ? [] : $service['factories']);

        foreach ($factories as $name => $factory) {
            if (is_subclass_of(
                $name,
                ListenerAggregateInterface::class,
                true
            )) {
                $this->listeners[] = $name;
            }
            $this->factories[$name] = $factory;
        }
    }

    /**
     * Retrieve factory definition from viewhelper config
     */
    private function registerViewHelper(): void
    {
        $view_helpers = $this->config->get('view_helpers', []);
        $factories = (empty($view_helpers['factories']) ? [] : $view_helpers['factories']);

        foreach ($factories as $name => $factory) {
            $this->factories[$name] = $factory;
        }
    }

    /**
     * Retrieve factory definition from console command config
     */
    private function registerCommand(): void
    {
        $console = $this->config->get('console', []);
        $factories = (empty($console['factories']) ? [] : $console['factories']);

        foreach ($factories as $name => $factory) {
            $this->factories[$name] = $factory;
        }
    }

    /**
     * Retrieve factory definition from HTTP action
     */
    private function registerHttpAction(): void
    {
        $action = $this->config->get('action', []);
        $factories = (empty($action['factories']) ? [] : $action['factories']);

        foreach ($factories as $name => $factory) {
            $this->factories[$name] = $factory;
        }
    }
}
