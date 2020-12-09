<?php

namespace App\Csrf;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class CsrfMiddleware {
    protected $field_name;
    protected $tokenManager;

    public function __construct(string $field_name, CsrfTokenManagerInterface $tokenManager) {
        $this->field_name = $field_name;
        $this->tokenManager = $tokenManager;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response {
        if (in_array($request->getMethod(), ["GET", "HEAD", "OPTIONS"])) {
            return $handler->handle($request);
        }

        // TODO : Need to validate csrf value
        $data = (array)$request->getParsedBody();
        $csrf_value = (empty($data[$this->field_name]) ? "" : $data[$this->field_name]);
        $csrfToken = new CsrfToken($this->tokenManager->getId(), $csrf_value);

        if ($this->tokenManager->isTokenValid($csrfToken)) {
            return $handler->handle($request);
        }

        $response = new Response();
        return $response->withHeader("Location", $request->getRequestTarget());
    }
}
