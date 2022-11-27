<?php

declare(strict_types=1);

namespace Itseasy\Csrf;

use Itseasy\Middleware\AbstractMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfMiddleware extends AbstractMiddleware
{
    const CSRF_HEADER = "X-CSRF-TOKEN";

    protected $field_name;
    protected $tokenManager;
    protected $session;

    public function __construct(
        string $field_name,
        CsrfTokenManagerInterface $tokenManager,
        SessionInterface $session
    ) {
        $this->field_name = $field_name;
        $this->tokenManager = $tokenManager;
        $this->session = $session;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (in_array($request->getMethod(), ["GET", "HEAD", "OPTIONS"])) {
            // get, head, options method is forbidden to have csrf header value
            if ($request->hasHeader(self::CSRF_HEADER)) {
                throw new HttpForbiddenException($request, "Invalid Request");
            }
            // get, head, options method is forbidden to have csrf value in query
            if (!empty($request->getQueryParams()[$this->field_name])) {
                throw new HttpForbiddenException($request, "Invalid Request");
            }
            return $handler->handle($request);
        }

        $route = $this->getRoute($request);
        if (empty($route)) {
            throw new HttpNotFoundException($request);
        }

        // Disable csrf check from route.config.php by setting csrf equal false in options.arguments
        // Slim route arguments only use string or null
        if (
            !is_null($route->getArgument("csrf"))
            and $route->getArgument("csrf") === "0"
        ) {
            return $handler->handle($request);
        }

        $csrf_value = [];

        // Retrieve from POST
        $data = (array)$request->getParsedBody();
        $csrf_value[] = (empty($data[$this->field_name]) ? "" : $data[$this->field_name]);

        // Retrieve from Header for ajax
        if ($request->hasHeader(self::CSRF_HEADER)) {
            $csrf_value[] = reset($request->getHeader(self::CSRF_HEADER, []));
        }

        // Get csrf value
        $csrf_value = reset(array_filter($csrf_value));
        $csrf_value = ($csrf_value === false ? null : $csrf_value);

        $csrfToken = new CsrfToken($this->tokenManager->getId(), $csrf_value);

        if ($this->tokenManager->isTokenValid($csrfToken)) {
            return $handler->handle($request);
        }

        $this->session->getFlashBag()->add("error", "Invalid request token, please resubmit");

        $response = new Response();
        return $response->withHeader("Location", $request->getRequestTarget());
    }
}
