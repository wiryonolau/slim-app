<?php

namespace Itseasy\Middleware;

use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface;

class BaseMiddleware {
    protected function getRoute(ServerRequestInterface $request) {
        $routeContext = RouteContext::fromRequest($request);
        return $routeContext->getRoute();
    }
}
