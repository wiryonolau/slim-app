<?php

namespace App\Session;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionMiddleware {
    private $session;

    public function __construct(Session $session) {
        $this->session = $session;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response {
        $this->session->start();
        return $handler->handle($request);
    }

}
