<?php
declare(strict_types = 1);

namespace Itseasy;

use ArrayObject;
use Exception;
use Slim\App;

class RouteCollection extends ArrayObject
{
    public function __construct(App $application)
    {
        $routes = [];
        foreach($application->getRouteCollector()->getRoutes() as $route) {
            if(is_string($route->getCallable())) {
                $addedRoute = new stdClass();
                $addedRoute->action = $route->getCallable();
                $addedRoute->identifier = $route->getIdentifier();
                $addedRoute->methods = $route->getMethods();
                $addedRoute->pattern = $route->getPattern();
                $adderRoute->name = $route->getName();

                $routes[] = $addedRoute;
            }
        }

        parent::__construct($routes);
    }
}
