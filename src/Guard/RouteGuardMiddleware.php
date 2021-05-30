<?php
declare(strict_types = 1);

namespace Itseasy\Guard;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response;
use Itseasy\Guard\RouteGuard;
use Itseasy\View\Helper\UrlHelper;
use Itseasy\Middleware\BaseMiddleware;
use Closure;

class RouteGuardMiddleware extends BaseMiddleware
{
    protected $routeGuard;
    protected $urlHelper;

    public function __construct(RouteGuard $routeGuard, UrlHelper $urlHelper)
    {
        $this->routeGuard = $routeGuard;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response
    {
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
            $query = $this->getRedirectQuery($request->getRequestTarget());
            $login_url = call_user_func_array($this->urlHelper, [$this->routeGuard->getOptions()->getLoginRoute(), $query]);
            $response = new Response();
            return $response->withHeader("Location", $login_url);
        }

        if ($has_identity == true and $allow_access == false) {
            throw new HttpForbiddenException($request, "Unauthorized Access");
        }

        return $handler->handle($request);
    }

    public function getRouteGuard() : RouteGuardInterface
    {
        return $this->routeGuard;
    }

    private function getRedirectQuery(string $target) : array {
        $target = trim($target);

        if (!$this->routeGuard->getOptions()->useRedirect()) {
            return [];
        }

        if ($target == "" ) {
            return [];
        }

        if ($target == "/") {
            return [];
        }

        if ($target == $this->routeGuard->getOptions()->getLoginRoute()) {
            return [];
        }

        return [
            "redirect" => base64_encode($target)
        ];
    }
}
