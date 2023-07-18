<?php

declare(strict_types=1);

namespace Itseasy;

use Exception;
use Slim\Routing\Route;

class RouteBuilder
{
    public static function addRoute(
        &$routeCollector,
        ?string $namespace = null,
        array $routes = []
    ): void {
        foreach ($routes as $name => $route) {
            $namespace = sprintf("%s/%s", $namespace, strval($name));
            if (!empty($route["options"]["redirect"])) {
                self::addActionRedirect($routeCollector, $namespace, $route);
                continue;
            }

            if (!empty($route["options"]["action"])) {
                self::addActionRoute($routeCollector, $namespace, $route);
                continue;
            }

            if (!empty($route["child_routes"])) {
                self::addGroupRoute($routeCollector, $namespace, $route);
                continue;
            }
        }
    }

    private static function addGroupRoute(
        &$routeCollector,
        ?string $namespace = null,
        array $route = []
    ): void {
        $path = $route["route"];

        $middlewares = [];
        if (!empty($route["middlewares"])) {
            $middlewares = $route["middlewares"];
        }

        $child_routes = [];
        if (!empty($route["child_routes"])) {
            $child_routes = $route["child_routes"];
        }

        if (count($child_routes)) {
            $addedRoute = $routeCollector->group(
                $path,
                function ($routeCollector) use ($namespace, $child_routes) {
                    self::addRoute($routeCollector, $namespace, $child_routes);
                }
            );

            self::addMiddleware(
                $addedRoute,
                $routeCollector->getContainer(),
                $middlewares
            );
        }
    }

    private static function addActionRedirect(
        &$routeCollector,
        ?string $namespace = null,
        array $route = []
    ): void {
        $path = $route["route"];
        $redirect = $route["options"]["redirect"];

        $arguments = [];
        if (!empty($route["options"]["arguments"])) {
            $arguments = $route["options"]["arguments"];
        }

        $middlewares = [];
        if (!empty($route["middlewares"])) {
            $middlewares = $route["middlewares"];
        }

        $child_routes = [];
        if (!empty($route["child_routes"])) {
            $child_routes = $route["child_routes"];
        }


        // Same as get
        $addedRoute = $routeCollector->redirect($path, $redirect, 301);
        $addedRoute->setArguments($arguments);
        $addedRoute->setName($namespace);

        self::addMiddleware(
            $addedRoute,
            $routeCollector->getContainer(),
            $middlewares
        );

        if (count($child_routes)) {
            $addedRoute = $routeCollector->group(
                $path,
                function ($routeCollector) use ($namespace, $child_routes) {
                    self::addRoute($routeCollector, $namespace, $child_routes);
                }
            );

            self::addMiddleware(
                $addedRoute,
                $routeCollector->getContainer(),
                $middlewares
            );
        }
    }

    private static function addActionRoute(
        &$routeCollector,
        ?string $namespace = null,
        array $route = []
    ): void {
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
        if (!empty($route["middlewares"])) {
            $middlewares = $route["middlewares"];
        }

        if (!$routeCollector->getContainer()->has($action)) {
            throw new Exception("Action $action not exist");
        }

        $child_routes = [];
        if (!empty($route["child_routes"])) {
            $child_routes = $route["child_routes"];
        }


        if (count($child_routes)) {
            $addedRoute = $routeCollector->group(
                $path,
                function ($routeCollector) use (
                    $namespace,
                    $method,
                    $action,
                    $arguments,
                    $child_routes
                ) {
                    $childRoute = $routeCollector->map($method, "", $action);
                    $childRoute->setArguments($arguments);

                    self::addRoute($routeCollector, $namespace, $child_routes);
                }
            );
        } else {
            $addedRoute = $routeCollector->map($method, $path, $action);
            $addedRoute->setName($namespace);
            $addedRoute->setArguments($arguments);
        }


        self::addMiddleware(
            $addedRoute,
            $routeCollector->getContainer(),
            $middlewares
        );
    }

    private static function addMiddleware(
        &$route,
        $container,
        array $middlewares = []
    ) {
        foreach ($middlewares as $middleware) {
            $middleware = $container->get($middleware);
            $route->add($middleware);
        }
    }
}
