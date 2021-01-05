<?php

namespace Itseasy\Navigation;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response;
use Itseasy\Middleware\BaseMiddleware;

class NavigationMiddleware extends BaseMiddleware {
    protected $config;

    public function __construct(Navigation $navigation) {
        $this->navigation = $navigation;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response {
        $this->navigation->setAttribute("request", $request);
        return $handler->handle($request);
    }
}
