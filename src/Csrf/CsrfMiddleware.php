<?php

namespace Itseasy\Csrf;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response;
use Itseasy\Middleware\BaseMiddleware;

class CsrfMiddleware extends BaseMiddleware {
    protected $field_name;
    protected $tokenManager;
    protected $session;

    public function __construct(string $field_name, CsrfTokenManagerInterface $tokenManager, SessionInterface $session) {
        $this->field_name = $field_name;
        $this->tokenManager = $tokenManager;
        $this->session = $session;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response {
        if (in_array($request->getMethod(), ["GET", "HEAD", "OPTIONS"])) {
            return $handler->handle($request);
        }

        $route = $this->getRoute($request);
        if (empty($route)) {
            throw new HttpNotFoundException($request);
        }
        if ($route->getArgument("csrf", true) == false) {
            return $handler->handle($request);
        }

        $csrf_value = [];

        // Retrieve from POST
        $data = (array)$request->getParsedBody();
        $csrf_value[] = (empty($data[$this->field_name]) ? "" : $data[$this->field_name]);

        // Retrieve from Header for ajax
        if ($request->hasHeader("X-CSRF-TOKEN")) {
            $csrf_value[] = $request->getHeader("X-CSRF-TOKEN");
        }

        $csrf_value = reset(array_filter($csrf_value));
        $csrfToken = new CsrfToken($this->tokenManager->getId(), $csrf_value);

        if ($this->tokenManager->isTokenValid($csrfToken)) {
            return $handler->handle($request);
        }

        $this->session->getFlashBag()->add("error", "Invalid request token, please resubmit");

        $response = new Response();
        return $response->withHeader("Location", $request->getRequestTarget());
    }
}
