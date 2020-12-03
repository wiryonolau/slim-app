<?php

namespace App\Guard;

use App\Guard\RouteGuard;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response;


class RouteGuardMiddleware {
    protected $routeGuard;

    public function __construct(RouteGuard $routeGuard) {
        $this->routeGuard = $routeGuard;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if (empty($route)) {
            throw new HttpNotFoundException($request);
        }

        $method = $request->getMethod();
        $action = $route->getCallable();

        if ($routeGuard->allow($method, $action)) {
            return $handler->handle($request);
        }
        $response = new Response();
        return $response->withStatus(403);
    }

    public function getRouteGuard() {
        return $this->routeGuard;
    }
}
