<?php

declare(strict_types=1);

namespace Itseasy\Http;

use Itseasy\Middleware\AbstractMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpException;
use Slim\Psr7\Response;

class JsonExceptionMiddleware extends AbstractMiddleware
{
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

            $response = new Response();
            $response->getBody()->write(json_encode($payload));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }
}
