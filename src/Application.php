<?php

namespace App;

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use DI;
use Psr\Container\ContainerInterface;
use Slim\Middleware\RoutingMiddleware;
use Slim\Middleware\ErrorMiddleware;

class Application {
    protected $config;
    protected $container;
    protected $application;
    protected $renderer = null;

    public function __construct($config_dirs) {
        if (!is_array($config_dirs)) {
            $config_dirs = [$config_dirs];
        }
        array_unshift($config_dirs, realpath(__DIR__."/../config"));

        $this->config = new Config($config_dirs);
        $this->container = new DI\Container();

        $this->buildContainer();
        $this->application = AppFactory::createFromContainer($this->container);
        $this->setRoute();
        $this->setMiddleware();
    }

    public function run() {
        $this->application->run();
    }

    public function setRenderer($renderer) {
        $this->renderer = $renderer;
        return $this;
    }

    private function setRoute() {
        self::addRoute($this->config->getConfig()["routes"], $this->application);
    }

    private function setMiddleware() {
        foreach ($this->config->getConfig()["middleware"]["middleware"] as $middleware) {
            if ($middleware == RoutingMiddleware::class) {
                $this->application->addRoutingMiddleware();
                continue;
            }

            if ($middleware == ErrorMiddleware::class) {
                $this->application->addErrorMiddleware(true, true, true);
                continue;
            }

            $this->application->add($this->container->get($middleware));
        }
    }

    private static function addRoute($routes, &$application) {
        foreach ($routes as $route) {
            $method = (empty($route["method"]) ? "GET" : $route["method"]);
            if (!is_array($method)) {
                $method = [$method];
            }

            $path = $route["route"];
            $action = $route["options"]["action"];

            if (!$application->getContainer()->has($action)) {
                throw new Exception("Action $action  not exist");
            }
            $controller = $application->getContainer()->get($action);

            $child_routes = (empty($route["child_routes"]) ? [] : $route["child_routes"]);
            if (count($child_routes)) {
                $application->group($path, function($application) use ($method, $path, $action, $child_routes) {
                    $application->map($method, "", $action);
                    self::addRoute($child_routes, $application);
                });
            } else {
                $application->map($method, $path, $action);
            }
        }
    }

    private function buildContainer() {
        $this->addDefinition('Config', $this->config);

        if (is_null($this->renderer)) {
            $this->addDefinition('HtmlRenderer', DI\factory($this->config->getConfig()["view"]["renderer"]));
        } else {
            $this->addDefinition('HtmlRenderer', $this->renderer);
        }

        # Build Service
        foreach($this->config->getConfig()["service"]["factories"] as $service => $factory) {
            $this->addDefinition($service, DI\factory($factory));
        }

        # Build Action
        foreach ($this->config->getConfig()["action"]["factories"] as $controller => $factory) {
            $this->addDefinition($controller, function(ContainerInterface $container, $args) use ($factory) {
                $obj = new $factory();
                $obj = $obj($container, $args);
                $obj->setRenderer($container->get("HtmlRenderer"));
                return $obj;
            });
        }
    }

    private function addDefinition($name, $class) {
        if ($class instanceof DI\Definition) {
            $this->container->set($name, $class);
        } else {
            $this->container->set($name, DI\factory($class));
        }
    }

}
