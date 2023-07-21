<?php

declare(strict_types=1);

namespace Itseasy\Http;

use Itseasy\Middleware\AbstractMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpException;
use Slim\Psr7\Response;

class HttpExceptionMiddleware extends AbstractMiddleware
{
    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function __invoke(
        Request $request,
        RequestHandler $handler
    ): Response {
        try {
            return $handler->handle($request);
        } catch (HttpException $httpException) {
            $this->logger->debug([
                $httpException->getCode(), $httpException->getMessage()
            ]);

            $response = new Response();

            // Check if this is a ajax request then prevent from sending html error
            if ($this->asJson($request)) {
                $payload = [
                    'jsonrpc' => '2.0',
                    'id' => time(),
                    'error' => [
                        'code' => -32603,
                        'message' => sprintf(
                            "%s %s",
                            $httpException->getCode(),
                            $httpException->getMessage()
                        )
                    ],
                ];

                $response->getBody()->write(json_encode($payload));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }

            $code = $httpException->getCode();
            $template = sprintf('/error/%d', $code);

            $variables = [
                'code' => $httpException->getCode(),
            ];

            return $this->view->render($response, $template, $variables);
        }
    }

    protected function asJson(Request $request): bool
    {
        try {
            if ($request->getQueryParams()["format"] == "json") {
                return true;
            }

            if ($request->getQueryParams()["output"] == "json") {
                return true;
            }

            if ($request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
