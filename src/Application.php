<?php

namespace App;

use DI;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Middleware\RoutingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use App\View;

class Application
{
    protected $config;
    protected $container;
    protected $application;
    protected $viewClass = View\View::class;

    public function __construct($config_dirs = null)
    {
        if (!is_array($config_dirs)) {
            $config_dirs = [$config_dirs];
        }
        array_unshift($config_dirs, __DIR__."/../config/*.config.php");

        $this->config = new Config($config_dirs);
        $this->container = new DI\Container();
        
        $this->buildContainer();
        $this->application = AppFactory::createFromContainer($this->container);
        $this->setRoute();
        $this->setMiddleware();
    }

    public function run()
    {
        $this->application->run();
    }
    
    public function getConfig() {
        return $this->config->getConfig();
    }

    public function getContainer() {
        return $this->container;
    }

    public function setViewClass(string $view) {
        $this->viewClass = $view; 
        return $this;
    }

    private function setRoute()
    {
        if (!empty($this->getConfig()["routes"])) {
            self::addRoute($this->getConfig()["routes"], $this->application);
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
                    $this->application->addErrorMiddleware(true, true, true);
                    continue;
                }

                $this->application->add($this->container->get($middleware));
            }
        }
    }

    private static function addRoute($routes, &$application)
    {
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
                $application->group($path, function ($application) use ($method, $path, $action, $child_routes) {
                    $application->map($method, "", $action);
                    self::addRoute($child_routes, $application);
                });
            } else {
                $application->map($method, $path, $action);
            }
        }
    }

    private function buildContainer()
    {
        $this->addDefinition('Config', $this->config);

        $this->addDefinition(HtmlRenderer::class, $this->getConfig()["view"]["renderer"]);

        # Build Service
        if (!empty($this->getConfig()["service"]["factories"])) {
            foreach ($this->getConfig()["service"]["factories"] as $service => $factory) {
                $this->addDefinition($service, $factory);
            }
        }

        # Build Action, Inject View
        if (!empty($this->getConfig()["action"]["factories"])) {
            foreach ($this->getConfig()["action"]["factories"] as $controller => $factory) {
                $this->addDefinition($controller, function (ContainerInterface $container, $args) use ($factory) {
                    $obj = new $factory();
                    $obj = $obj($container, $args);
                    $view = new $this->viewClass();
                    $view->setRenderer($container->get(HtmlRenderer::class));
                    $obj->setView($view);
                    return $obj;
                });
            }
        }
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
