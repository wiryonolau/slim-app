<?php

namespace Itseasy\Guard;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response;
use Itseasy\Guard\RouteGuard;
use Itseasy\View\Helper\UrlHelper;
use Itseasy\Middleware\BaseMiddleware;
use Closure;

class RouteGuardMiddleware extends BaseMiddleware {
    protected $routeGuard;
    protected $urlHelper;

    public function __construct(RouteGuard $routeGuard, UrlHelper $urlHelper) {
        $this->routeGuard = $routeGuard;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response {
        $route = $this->getRoute($request);

        if (empty($route)) {
            throw new HttpNotFoundException($request);
        }

        $method = $request->getMethod();
        $action = $route->getCallable();

        // Guard can only check if action is string
        if ($action instanceof Closure) {
            return $handler->handle($request);
        }

        // Guest Access
        $allow_access = $this->routeGuard->allow($method, $action);
        $has_identity = $this->routeGuard->getIdentityProvider()->hasIdentity();

        if ($has_identity == false and $allow_access == false) {
            $login_url = call_user_func_array($this->urlHelper,[$this->routeGuard->getOptions()->getLoginRoute()]);
            $response = new Response();
            return $response->withHeader("Location", $login_url);
        }

        if ($has_identity == true and $allow_access == false) {
            $response = new Response();
            return $response->withStatus(403);
        }

        return $handler->handle($request);
    }

    public function getRouteGuard() {
        return $this->routeGuard;
    }
}
