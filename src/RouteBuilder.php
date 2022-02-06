<?php
declare(strict_types = 1);

namespace Itseasy;

use Slim\App;

class RouteBuilder
{
    protected $application;

    public function __construct(
        App $application,
        ?string $namespace = null,
        array $routes = []
    ) {
        $this->application = $application;

        $this->addRoute(
            $namespace,
            $routes
        );
    }

    public static function factory(
        App $application,
        ?string $namespace = null,
        array $routes = []
    ) : App {

        $route = new RouteBuilder(
            $application,
            $namespace,
            $routes
        );

        return $route->getApplication();
    }


    public function getApplication() : App
    {
        return $this->application;
    }

    private function addRoute(
        ?string $namespace = null,
        array $routes = []
    ) : void {
        foreach ($routes as $name => $route) {
            $namespace = sprintf("%s/%s", $namespace, strval($name));
            if (!empty($route["options"]["redirect"])) {
                $this->addActionRedirect($namespace, $route);
                continue;
            }

            if (!empty($route["options"]["action"])) {
                $this->addActionRoute($namespace, $route);
                continue;
            }

            if (!empty($route["child_routes"])) {
                $this->addGroupRoute($namespace, $route);
                continue;
            }
        }
    }

    private function addGroupRoute(
        ?string $namespace = null,
        array $route = []
    ) : void {
        $path = $route["route"];

        $arguments = [];
        if (!empty($route["options"]["arguments"])) {
            $arguments = $route["options"]["arguments"];
        }

        $middlewares = [];
        if (!empty($route["options"]["middlewares"])) {
            $middlewares = $route["options"]["middlewares"];
        }

        $child_routes = [];
        if (!empty($route["child_routes"])) {
            $child_routes = $route["child_routes"];
        }

        if (count($child_routes)) {
            $addedRoute = $this->application->group($path, function () use ($namespace, $child_routes) {
                $this->addRoute($namespace, $child_routes);
            });
        }

        $this->addMiddleware($addedRoute, $middlewares);
    }

    private function addActionRedirect(
        ?string $namespace = null,
        array $route = []
        ) : void {
        $path = $route["route"];
        $redirect = $route["options"]["redirect"];

        $arguments = [];
        if (!empty($route["options"]["arguments"])) {
            $arguments = $route["options"]["arguments"];
        }

        $middlewares = [];
        if (!empty($route["options"]["middlewares"])) {
            $middlewares = $route["options"]["middlewares"];
        }

        $child_routes = [];
        if (!empty($route["child_routes"])) {
            $child_routes = $route["child_routes"];
        }

        // Same as get
        $addedRoute = $this->application->redirect($path, $redirect, 301);

        if (count($arguments)) {
            $arguments = array_map(function ($argument) {
                if (is_bool($argument) and $argument === false) {
                    return "0";
                }
                return strval($argument);
            }, $arguments);
            $addedRoute->setArguments($arguments);
        }

        $addedRoute->setName($namespace);

        if (count($child_routes)) {
            $addedRoute = $this->application->group($path, function () use ($namespace, $child_routes) {
                $this->addRoute($namespace, $child_routes);
            });
        }

        $this->addMiddleware($addedRoute, $middlewares);
    }

    private function addActionRoute(
        ?string $namespace = null,
        array $route = []
        ) : void {
        $method = (empty($route["method"]) ? "GET" : $route["method"]);

        if (!is_array($method)) {
            $method = [$method];
        }

        $path = $route["route"];
        $action = $route["options"]["action"];

        $arguments = [];
        if (!empty($route["options"]["arguments"])) {
            $arguments = $route["options"]["arguments"];
        }

        $middlewares = [];
        if (!empty($route["options"]["middleware"])) {
            $middlewares = $route["options"]["middleware"];
        }

        if (!$this->application->getContainer()->has($action)) {
            throw new Exception("Action $action not exist");
        }

        $child_routes = [];
        if (!empty($route["child_routes"])) {
            $child_routes = $route["child_routes"];
        }

        if (count($child_routes)) {
            $addedRoute = $this->application->group($path, function () use ($namespace, $method, $action, $child_routes) {
                $this->application->map($method, "", $action);
                $this->addRoute($namespace, $child_routes);
            });
        } else {
            $addedRoute = $this->application->map($method, $path, $action);
            $addedRoute->setName($namespace);

            if (count($arguments)) {
                $arguments = array_map(function ($argument) {
                    if (is_bool($argument) and $argument === false) {
                        return "0";
                    }
                    return strval($argument);
                }, $arguments);
                $addedRoute->setArguments($arguments);
            }
        }

        $this->addMiddleware($addedRoute, $middlewares);
    }

    private function addMiddleware($route, array $middlewares = [])
    {
        foreach ($middlewares as $middleware) {
            $middleware = $this->application->getContainer()->get($middleware);
            $route->add($middleware);
        }
    }
}
