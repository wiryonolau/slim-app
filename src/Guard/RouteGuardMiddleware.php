<?php

namespace App\Guard;

use App\Guard\RouteGuard;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response;
use App\View\Helper\UrlHelper;

class RouteGuardMiddleware {
    protected $routeGuard;
    protected $urlHelper;

    public function __construct(RouteGuard $routeGuard, UrlHelper $urlHelper) {
        $this->routeGuard = $routeGuard;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if (empty($route)) {
            throw new HttpNotFoundException($request);
        }

        $method = $request->getMethod();
        $action = $route->getCallable();

        if ($this->routeGuard->hasIdentity()) {
            if ($this->routeGuard->allow($method, $action)) {
                return $handler->handle($request);
            }

            $response = new Response();
            return $response->withStatus(403);
        } else {
            $login_url = $this->urlHelper($this->routeGuard->getOptions()->getLoginRoute());
            $response = new Response();
            return $response->withHeader("Location", $login_url);
        }
    }

    public function getRouteGuard() {
        return $this->routeGuard;
    }
}
