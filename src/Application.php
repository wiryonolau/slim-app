<?php

declare(strict_types=1);

namespace Itseasy;

use Exception;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Log as LaminasLog;
use Laminas\Log\LoggerInterface;
use Laminas\Stdlib\ArrayUtils;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application
{
    const APP_CONSOLE = 'console';
    const APP_HTTP = 'http';

    protected $config_array = [];
    protected $config = null;
    protected $container = null;
    protected $logger = null;
    protected $eventManager = null;
    protected $containerProvider = ServiceManager\DIServiceManager::class;
    protected $application = null;

    protected $errorRenderer = [];
    protected $errorHandler = null;
    protected $error_options = [true, true, true, null];

    protected $log_level = LaminasLog\Logger::INFO;
    protected $log_file = 'php://stderr';
    protected $log_format = '%timestamp% %priorityName% (%priority%): %message%';
    protected $log_time_format = 'c';

    protected $options = [
        'config_path' => [
            __DIR__ . '/../config/*.config.php',
        ],
        'container_provider' => ServiceManager\DIServiceManager::class,
        'application_type' => 'http',
        'console' => [
            'name' => '',
            'version' => '',
        ],
    ];

    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'config_path':
                    $this->setConfigPath($value);
                    break;
                case 'application_type':
                    $this->setApplicationType($value);
                    break;
                case 'console':
                    $this->setConsoleOptions($value);
                    break;
                case 'logger':
                    $this->setLogger($value);
                    break;
                case 'event_manager':
                    $this->setEventManager($value);
                    break;
                case 'module':
                    $this->setModule($value);
                    break;
                case 'container_provider':
                    $this->setContainerProvider($value);
                    break;
                default:
            }
        }
    }

    public function setConfigPath($path, $overwrite = false): self
    {
        if (is_array($path)) {
            $this->options['config_path'] = ArrayUtils::merge(
                $this->options['config_path'],
                $path,
                $overwrite
            );
        } else {
            $this->options['config_path'][] = $path;
        }

        return $this;
    }

    public function setContainerProvider(string $containerProvider = ServiceManager\DIServiceManager::class)
    {
        if (!in_array($containerProvider, [
            ServiceManager\DIServiceManager::class,
            ServiceManager\LaminasServiceManager::class,
        ])) {
            throw new Exception(sprintf('%s Container provider not supported', $containerProvider));
        }
        $this->containerProvider = $containerProvider;

        return $this;
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set default log level, not applicable for custom logger class.
     **/
    public function setLogLevel(int $log_level = LaminasLog\Logger::INFO): self
    {
        $this->log_level = $log_level;

        return $this;
    }

    /**
     * Set default log file, not applicable for custom logger class.
     **/
    public function setLogFile(string $log_file = 'php://stderr'): self
    {
        $this->log_file = $log_file;

        return $this;
    }

    /**
     * Set default log format, not applicable for custom logger class.
     **/
    public function setLogFormat(
        string $log_format = '%timestamp% %message%',
        string $log_time_format = 'c'
    ): self {
        $this->log_format = $log_format;
        $this->log_time_format = $log_time_format;

        return $this;
    }

    public function setEventManager(EventManagerInterface $eventManager): self
    {
        $this->eventManager = $eventManager;

        return $this;
    }

    public function setModule(array $modules = []): self
    {
        foreach ($modules as $module) {
            $this->addModule($module);
        }

        return $this;
    }

    public function addModule(string $class): self
    {
        if (method_exists($class, 'getConfigPath')) {
            $module_config = call_user_func([$class, 'getConfigPath']);
            array_splice($this->options['config_path'], 1, 0, $module_config);
        }

        // Laminas module
        if (method_exists($class, 'getConfig')) {
            $module = new $class();
            $module_config = $module->getConfig();

            $this->config_array = ArrayUtils::merge($this->config_array, [
                'service' => isset($module_config['service_manager']) ? $module_config['service_manager'] : [],
                'view_helpers' => isset($module_config['view_helpers']) ? $module_config['view_helpers'] : [],
            ]);
        }

        return $this;
    }

    public function setApplicationType(string $type): self
    {
        if (in_array($type, [self::APP_CONSOLE, self::APP_HTTP])) {
            $this->options['application_type'] = $type;

            return $this;
        }
    }

    public function setConsoleOptions(array $options = []): void
    {
        $this->options['console'] = ArrayUtils::merge(
            $this->options['console'],
            $options
        );
    }

    public function setErrorRenderer(string $contentType, string $errorRenderer): self
    {
        $this->errorRenderer[$contentType] = $errorRenderer;

        return $this;
    }

    public function setErrorHandler(callable $handler): self
    {
        $this->errorHandler = $handler;

        return $this;
    }

    public function setErrorOptions(
        bool $display_error_details = true,
        bool $log_errors = true,
        bool $log_error_details = true,
        ?PsrLoggerInterface $logger = null
    ): self {
        $this->error_options = [
            $display_error_details,
            $log_errors,
            $log_error_details,
            $logger,
        ];

        return $this;
    }

    public function build(): self
    {
        $this->config = new Config($this->options['config_path'], $this->config_array);

        if (is_null($this->logger)) {
            $this->buildLogger();
        }

        $this->container = $this->containerProvider::factory(
            $this->config,
            $this->logger,
            $this->eventManager
        );

        if ($this->options['application_type'] == self::APP_HTTP) {
            $this->application = AppFactory::createFromContainer($this->container);
            $this->setRoute();
            $this->setMiddleware();

            // Iterable complete route collection for http
            $routeCollection = new RouteCollection($this->application);
            $routeCollection->lock();

            $this->container->setService('applicationroute', $routeCollection);
            $this->container->setService('ApplicationRoute', $routeCollection);
        } elseif ($this->options['application_type'] == self::APP_CONSOLE) {
            $this->application = new ConsoleApplication(
                $this->options['console']['name'],
                $this->options['console']['version']
            );
            $this->setCommand();
        }

        return $this;
    }

    public function run(): void
    {
        if (
            is_null($this->config)
            or is_null($this->container)
            or is_null($this->application)
        ) {
            $this->build();
        }
        $this->application->run();
    }

    public function getConfig(): array
    {
        return $this->config->getConfig();
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return Slim\App|Symfony\Component\Console\Application|null
     */
    public function getApplication()
    {
        return $this->application;
    }

    private function setCommand(): void
    {
        $commands = [];

        $commands_config = [];
        if (!empty($this->getConfig()['console']['commands'])) {
            $commands_config = $this->getConfig()['console']['commands'];
        }

        foreach ($commands_config as $command) {
            $commands[] = $this->container->get($command);
        }

        $this->application->addCommands($commands);
    }

    private function setRoute(): void
    {
        if (!empty($this->getConfig()['routes'])) {
            RouteBuilder::addRoute(
                $this->application,
                null,
                $this->getConfig()['routes']
            );
        }
    }

    private function setMiddleware(): void
    {
        $middlewares = [];
        if (!empty($this->getConfig()['middleware']['middleware'])) {
            $middlewares = $this->getConfig()['middleware']['middleware'];
        }

        foreach ($middlewares as $middleware) {
            if ($middleware == RoutingMiddleware::class) {
                $this->application->addRoutingMiddleware();
                continue;
            }

            if ($middleware == ErrorMiddleware::class) {
                $errorMiddleware = call_user_func_array(
                    [$this->application, 'addErrorMiddleware'],
                    $this->error_options
                );
                if (!is_null($this->errorHandler)) {
                    $errorMiddleware->setDefaultErrorHandler($this->errorHandler);
                }
                if (count($this->errorRenderer)) {
                    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
                    foreach ($this->errorRenderer as $content_type => $renderer) {
                        $errorHandler->registerErrorRenderer(
                            $content_type,
                            $renderer
                        );
                    }
                }
                continue;
            }

            if ($this->container->has($middleware)) {
                $this->application->add($this->container->get($middleware));
                continue;
            }

            // Allow special middleware without factory
            // Some slim middleware doesn't use factory
            if (is_string($middleware) and class_exists($middleware)) {
                $this->application->add(new $middleware());
                continue;
            }

            throw new Exception(sprintf('Middleware %s not found', $middleware));
        }
    }

    private function buildLogger(): void
    {
        $logger = new LaminasLog\Logger([
            'writers' => [
                'stderr' => [
                    'name' => 'stream',
                    'priority' => 1,
                    'options' => [
                        'stream' => $this->log_file,
                        'formatter' => [
                            'name' => LaminasLog\Formatter\Simple::class,
                            'options' => [
                                'format' => $this->log_format,
                                'dateTimeFormat' => $this->log_time_format,
                            ],
                        ],
                        'filters' => [
                            'priority' => [
                                'name' => 'priority',
                                'options' => [
                                    'operator' => '<=',
                                    'priority' => $this->log_level,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'processors' => [
                'requestid' => [
                    'name' => LaminasLog\Processor\RequestId::class,
                ],
            ],
        ]);

        $this->setLogger($logger);

        if (is_null($this->error_options[3])) {
            $psrLogger = new LaminasLog\PsrLoggerAdapter($logger);
            $this->error_options[3] = $psrLogger;
        }
    }
}
