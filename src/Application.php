<?php

namespace Itseasy;

use DI;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Middleware\RoutingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Laminas\Stdlib\ArrayUtils;
use Symfony\Component\Console\Application as ConsoleApplication;
use Itseasy\View;
use Itseasy\Action\BaseAction;

class Application
{
    const APP_CONSOLE = "console";
    const APP_HTTP = "http";

    protected $config = null;
    protected $container = null;
    protected $application = null;

    protected $errorRenderer = [];
    protected $errorHandlers = null;
    protected $error_options = [true, true, true , null];

    protected $options = [
        "config_path" => [
            __DIR__."/../config/*.config.php"
        ],
        "container_cache_path" => null,
        "application_type" => "http",
        "console" => [
            "name" => "",
            "version" => ""
        ]
    ];

    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case "config_path":
                    $this->setConfigPath($value);
                    break;
                case "container_cache_path":
                    $this->setContainerCachePath($value);
                    break;
                case "application_type" :
                    $this->setApplicationType($value);
                    break;
                case "console":
                    $this->setConsoleOptions($value);
                    break;
                default:
            }
        }
    }

    public function setConfigPath($path) : self
    {
        if (is_array($path)) {
            $this->options["config_path"] = ArrayUtils::merge($this->options["config_path"], $path);
        } else {
            $this->options["config_path"][] = $path;
        }
        return $this;
    }

    public function setContainerCachePath(string $path) : self
    {
        $this->options["container_cache_path"] = $path;
        return $this;
    }

    public function setApplicationType(string $type) : self
    {
        if (in_array($type, [self::APP_CONSOLE, self::APP_HTTP])) {
            $this->options["application_type"] = $type;
            return $this;
        }
    }

    public function setConsoleOptions(array $options = []) {
        $this->options["console"] = ArrayUtils::merge($this->options["console"], $options);
    }

    public function setErrorRenderer(string $contentType, string $errorRenderer) : self {
        $this->errorRenderer[$contentType] = $errorRenderer;
        return $this;
    }

    public function setErrorHandler(callable $handler) : self {
        $this->errorHandler = $handler;
        return $this;
    }

    public function setErrorOptions(bool $display_error_details = true,
        bool $log_errors = true, bool $log_error_details = true, ?LoggerInterface $logger = null
    ) : self {
        $this->error_options = [
            $display_error_details,
            $log_errors,
            $log_error_details,
            $logger
        ];
        return $this;
    }

    public function build()
    {
        $this->config = new Config($this->options["config_path"]);

        $containerBuilder = new DI\ContainerBuilder();
        if (!is_null($this->options["container_cache_path"])) {
            $containerBuilder->enableCompiliation($this->options["container_cache_path"]);
        }
        $this->container = $containerBuilder->build();
        $this->buildContainer();

        if ($this->options["application_type"] == self::APP_HTTP) {
            $this->application = AppFactory::createFromContainer($this->container);
            $this->setRoute();
            $this->setMiddleware();
        } else if ($this->options["application_type"] == self::APP_CONSOLE) {
            $this->application = new ConsoleApplication($this->options["console"]["name"], $this->options["console"]["version"]);
            $this->setCommand();
        }
    }

    public function run()
    {
        if (is_null($this->config) or is_null($this->container) or is_null($this->application)) {
            $this->build();
        }
        $this->application->run();
    }

    public function getConfig()
    {
        return $this->config->getConfig();
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getApplication()
    {
        return $this->application;
    }

    private function setCommand() {
        $commands = [];
        if (!empty($this->getConfig()["console"]["commands"])) {
            foreach ($this->getConfig()["console"]["commands"] as $command) {
                $commands[] = $this->container->get($command);
            }
        }
        $this->application->addCommands($commands);
    }

    private function setRoute()
    {
        if (!empty($this->getConfig()["routes"])) {
            self::addRoute(null, $this->getConfig()["routes"], $this->application);
        }
    }

    private function setMiddleware()
    {
        if (!empty($this->getConfig()["middleware"]["middleware"])) {
            foreach ($this->getConfig()["middleware"]["middleware"] as $middleware) {
                if ($middleware == RoutingMiddleware::class) {
                    $this->application->addRoutingMiddleware();
                    continue;
                }

                if ($middleware == ErrorMiddleware::class) {

                    $errorMiddleware = call_user_func_array([$this->application, "addErrorMiddleware"], $this->error_options);
                    if (!is_null($this->errorHandler)) {
                        $errorMiddleware->setDefaultErrorHandler($this->errorHandler);
                    }
                    if (count($this->errorRenderer)) {
                        $errorHandler = $errorMiddleware->getDefaultErrorHandler();
                        foreach ($this->errorRenderer as $content_type => $renderer) {
                            $errorHandler->registerErrorRenderer($content_type, $renderer);
                        }
                    }
                    continue;
                }

                $this->application->add($this->container->get($middleware));
            }
        }
    }

    private static function addRoute(?string $namespace, array $routes, &$application)
    {
        foreach ($routes as $name => $route) {
            $namespace = sprintf("%s/%s", $namespace, strval($name));
            if (!empty($route["options"]["redirect"])) {
                self::addActionRedirect($namespace, $route, $application);
                continue;
            }

            if (!empty($route["options"]["action"])) {
                self::addActionRoute($namespace, $route, $application);
                continue;
            }

            if (!empty($route["child_routes"])) {
                self::addGroupRoute($namespace, $route, $application);
                continue;
            }
        }
    }

    private static function addGroupRoute($namespace, $route, &$application) {
        $path = $route["route"];
        $arguments = (empty($route["options"]["arguments"]) ? [] : $route["options"]["arguments"]);
        $middleware = (empty($route["options"]["middleware"]) ? null : $route["options"]["middleware"]);
        $child_routes = (empty($route["child_routes"]) ? [] : $route["child_routes"]);

        if (count($child_routes)) {
            $addedRoute = $application->group($path, function ($application) use ($namespace, $child_routes) {
                self::addRoute($namespace, $child_routes, $application);
            });

            if (!is_null($middleware)) {
                $middleware = $application->getContainer()->get($middleware);
                $addedRoute->add($middleware);
            }
        }
    }

    private static function addActionRedirect($namespace, $route, &$application) {
        $path = $route["route"];
        $redirect = $route["options"]["redirect"];
        $arguments = (empty($route["options"]["arguments"]) ? [] : $route["options"]["arguments"]);

        $child_routes = (empty($route["child_routes"]) ? [] : $route["child_routes"]);

        // Same as get
        $addedRoute = $application->redirect($path, $redirect, 301);

        if (count($arguments)) {
            $addedRoute->addArguments($arguments);
        }

        if (!is_null($middleware)) {
            $middleware = $application->getContainer()->get($middleware);
            $addedRoute->add($middleware);
        }

        $addedRoute->setName($namespace);

        if (count($child_routes)) {
            $application->group($path, function ($application) use ($namespace, $path, $redirect, $child_routes) {
                self::addRoute($namespace, $child_routes, $application);
            });

            if (!is_null($middleware)) {
                $middleware = $application->getContainer()->get($middleware);
                $addedRoute->add($middleware);
            }
        }
    }

    private static function addActionRoute($namespace, $route, &$application) {
        $method = (empty($route["method"]) ? "GET" : $route["method"]);
        if (!is_array($method)) {
            $method = [$method];
        }

        $path = $route["route"];
        $action = $route["options"]["action"];
        $arguments = (empty($route["options"]["arguments"]) ? [] : $route["options"]["arguments"]);
        $middleware = (empty($route["options"]["middleware"]) ? null : $route["options"]["middleware"]);

        if (!$application->getContainer()->has($action)) {
            throw new \Exception("Action $action  not exist");
        }

        $child_routes = (empty($route["child_routes"]) ? [] : $route["child_routes"]);
        if (count($child_routes)) {
            $addedRoute = $application->group($path, function ($application) use ($namespace, $method, $path, $action, $child_routes) {
                $application->map($method, "", $action);
                self::addRoute($namespace, $child_routes, $application);
            });
        } else {
            $addedRoute = $application->map($method, $path, $action);
            $addedRoute->setName($namespace);

            if (count($arguments)) {
                $addedRoute->addArguments($arguments);
            }
        }

        if (!is_null($middleware)) {
            $middleware = $application->getContainer()->get($middleware);
            $addedRoute->add($middleware);
        }

    }

    private function buildContainer()
    {
        $this->addDefinition('Config', $this->config);

        # Build Service
        if (!empty($this->getConfig()["service"]["factories"])) {
            foreach ($this->getConfig()["service"]["factories"] as $service => $factory) {
                $this->addDefinition($service, $factory);
            }
        }

        # Build Console
        if ($this->options["application_type"] == self::APP_CONSOLE) {
            if (!empty($this->getConfig()["console"]["factories"])) {
                array_walk($this->getConfig()["console"]["factories"], [$this, "registerCommand"]);
            }
        }

        # Build Action, Inject View
        if ($this->options["application_type"] == self::APP_HTTP) {
            if (!empty($this->getConfig()["action"]["factories"])) {
                array_walk($this->getConfig()["action"]["factories"], [$this, "registerHttpAction"]);
            }
        }
    }

    private function registerCommand($factory, $command) {
        $this->addDefinition($command, $factory);
    }

    private function registerHttpAction($factory, $action) {
        $this->addDefinition($action, function (ContainerInterface $container, $args) use ($action, $factory) {
            if ($factory instanceof \Di\Definition\Helper\DefinitionHelper) {
                $obj = new $action;
            } else {
                $obj = new $factory();
                $obj = $obj($container, $args);
            }

            $viewClass = $this->getConfig()["view"]["class"];
            $rendererClass = $this->getConfig()["view"]["renderer"];
            $default_layout = $this->getConfig()["view"]["default_layout"];

            if ($obj instanceof BaseAction) {
                $view = new $viewClass();
                $view->setRenderer($container->get($rendererClass));
                $view->setLayout($default_layout);
                $obj->setView($view);
            }
            return $obj;
        });
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
