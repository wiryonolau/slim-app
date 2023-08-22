<?php

declare(strict_types=1);

namespace Itseasy\Guard;

use Closure;
use HttpRequest;
use Itseasy\Middleware\AbstractMiddleware;
use Itseasy\View\Helper\UrlHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response;

class RouteGuardMiddleware extends AbstractMiddleware
{
    protected $routeGuard;
    protected $urlHelper;

    public function __construct(RouteGuard $routeGuard, UrlHelper $urlHelper)
    {
        $this->routeGuard = $routeGuard;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
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
            $this->logger->debug(
                vsprintf('Forbidden access %s %s, Has Identity : %s, Has Access : %s', [
                    $method,
                    $request->getRequestTarget(),
                    ($has_identity ? 'true' : 'false'),
                    ($allow_access ? 'true' : 'false'),
                ])
            );

            if (HttpRequest::asJson($request)) {
                throw new HttpForbiddenException($request, 'Unauthorized Access');
            }

            $query = $this->getRedirectQuery($request->getRequestTarget());
            $login_url = call_user_func_array(
                $this->urlHelper,
                [$this->routeGuard->getOptions()->getLoginRoute(), $query]
            );
            $response = new Response();

            return $response->withHeader('Location', $login_url);
        }

        if ($has_identity == true and $allow_access == false) {
            throw new HttpForbiddenException($request, 'Unauthorized Access');
        }

        return $handler->handle($request);
    }

    public function getRouteGuard(): RouteGuardInterface
    {
        return $this->routeGuard;
    }

    private function getRedirectQuery(string $target): array
    {
        $target = trim($target);

        if (!$this->routeGuard->getOptions()->useRedirect()) {
            return [];
        }

        if ($target == '') {
            return [];
        }

        if ($target == '/') {
            return [];
        }

        if ($target == $this->routeGuard->getOptions()->getLoginRoute()) {
            return [];
        }

        return [
            'redirect' => base64_encode($target),
        ];
    }
}
