<?php

namespace Itseasy;

class RouteBuilder
{
    public static function addRoute(
        Application &$application,
        ?string $namespace = null,
        array $routes = []
    ) : void {
        foreach ($routes as $name => $route) {
            $namespace = sprintf("%s/%s", $namespace, strval($name));
            if (!empty($route["options"]["redirect"])) {
                self::addActionRedirect($application, $namespace, $child_routes);
                continue;
            }

            if (!empty($route["options"]["action"])) {
                self::addActionRoute($application, $namespace, $child_routes);
                continue;
            }

            if (!empty($route["child_routes"])) {
                self::addGroupRoute($application, $namespace, $child_routes);
                continue;
            }
        }
    }

    private static function addGroupRoute(
        Application &$application,
        ?string $namespace = null,
        array $routes = []
    ) : void {
        $path = $route["route"];

        $arguments = [];
        if (!empty($route["options"]["arguments"])) {
            $arguments = $route["options"]["arguments"];
        }

        $middleware = [];
        if (!empty($route["options"]["middleware"])) {
            $middleware = $route["options"]["middleware"];
        }

        $child_routes = [];
        if (!empty($route["child_routes"])) {
            $child_routes = $route["child_routes"];
        }

        if (count($child_routes)) {
            $addedRoute = $application->group($path, function ($application) use ($namespace, $child_routes) {
                self::addRoute($application, $namespace, $child_routes);
            });

            if (!is_null($middleware)) {
                $middleware = $application->getContainer()->get($middleware);
                $addedRoute->add($middleware);
            }
        }
    }

    private static function addActionRedirect(
        Application &$application,
        ?string $namespace = null,
        array $routes = []
        ) : void {
        $path = $route["route"];
        $redirect = $route["options"]["redirect"];

        $arguments = [];
        if (!empty($route["options"]["arguments"])) {
            $arguments = $route["options"]["arguments"];
        }

        $child_routes = [];
        if (!empty($route["child_routes"])) {
            $child_routes = $route["child_routes"];
        }

        // Same as get
        $addedRoute = $application->redirect($path, $redirect, 301);

        if (count($arguments)) {
            $arguments = array_map(function ($argument) {
                if (is_bool($argument) and $argument === false) {
                    return "0";
                }
                return strval($argument);
            }, $arguments);
            $addedRoute->setArguments($arguments);
        }

        if (!is_null($middleware)) {
            $middleware = $application->getContainer()->get($middleware);
            $addedRoute->add($middleware);
        }

        $addedRoute->setName($namespace);

        if (count($child_routes)) {
            $application->group($path, function ($application) use ($namespace, $path, $redirect, $child_routes) {
                self::addRoute($application, $namespace, $child_routes);
            });

            if (!is_null($middleware)) {
                $middleware = $application->getContainer()->get($middleware);
                $addedRoute->add($middleware);
            }
        }
    }

    public static function addActionRoute(
        Application &$application,
        ?string $namespace = null,
        array $routes = []
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

        $middleware = [];
        if (!empty($route["options"]["middleware"])) {
            $middleware = $route["options"]["middleware"];
        }

        if (!$application->getContainer()->has($action)) {
            throw new Exception("Action $action not exist");
        }

        $child_routes = [];
        if (!empty($route["child_routes"])) {
            $child_routes = $route["child_routes"];
        }

        if (count($child_routes)) {
            $addedRoute = $application->group($path, function ($application) use ($namespace, $method, $path, $action, $child_routes) {
                $application->map($method, "", $action);
                self::addRoute($application, $namespace, $child_routes);
            });
        } else {
            $addedRoute = $application->map($method, $path, $action);
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

        if (!is_null($middleware)) {
            $middleware = $application->getContainer()->get($middleware);
            $addedRoute->add($middleware);
        }
    }
}
