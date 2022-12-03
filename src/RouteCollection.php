<?php

declare(strict_types=1);

namespace Itseasy;

use ArrayObject;
use Exception;
use Slim\App;
use stdClass;

class RouteCollection extends ArrayObject
{
    protected $lock = false;

    public function __construct(App $application)
    {
        $routes = [];
        foreach ($application->getRouteCollector()->getRoutes() as $route) {
            $addedRoute = new stdClass();
            $addedRoute->identifier = $route->getIdentifier();
            $addedRoute->methods = $route->getMethods();
            $addedRoute->pattern = $route->getPattern();
            $addedRoute->name = $route->getName();
            if (is_string($route->getCallable())) {
                $addedRoute->action = $route->getCallable();
            } else {
                $addedRoute->action = "closure";
            }
            $routes[] = $addedRoute;
        }

        parent::__construct($routes);
    }

    public function lock(): void
    {
        $this->lock = true;
    }

    public function getRouteByPath(string $path): ?stdClass
    {
        foreach ($this as $route) {
            $pattern = addcslashes($route->pattern, "/");

            $pattern = preg_replace_callback('/{(.*?)}/', function ($matches) {
                if (count($matches)) {
                    $regex = explode(":", $matches[1]);
                    return $regex[1];
                }
            }, $pattern);

            preg_match('/^' . $pattern . '$/i', $path, $matches);
            if (count($matches)) {
                return $route;
            }
        }
        return null;
    }

    // Group route by action, useful for permission list
    public function listRouteMethods(): array
    {
        $routes = [];
        foreach ($this as $route) {
            $addedRoute = new stdClass();
            $addedRoute = $route;

            if (isset($routes[$addedRoute->action])) {
                $routes[$addedRoute->action]->methods = array_values(
                    array_unique(
                        array_merge(
                            $routes[$addedRoute->action]->methods,
                            $route->methods
                        )
                    )
                );
                continue;
            }
            $routes[$addedRoute->action] = $addedRoute;
        }

        return $routes;
    }

    #[\ReturnTypeWillChange]
    public function append($value)
    {
        if ($this->lock) {
            throw new Exception("Readonly object");
        }
        parent::append($value);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        if ($this->lock) {
            throw new Exception("Readonly object");
        }
        parent::offsetSet($key, $value);
    }
}
