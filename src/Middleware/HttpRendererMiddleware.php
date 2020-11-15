<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\PhpRenderer;
use Slim\Psr7\Response;

class HttpRendererMiddleware {
    public function __construct(PhpRenderer $renderer) {
        $this->renderer = $renderer;
    }

    public function __invoke(Request $request, RequestHandler $handler) {
        $response = $handler->handle($request);

        return $response;
    }
}
