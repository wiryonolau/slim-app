<?php

declare(strict_types=1);

namespace Itseasy\Http;

use Itseasy\Http\HttpRequest;
use Itseasy\Middleware\AbstractMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpException;
use Slim\Psr7\Response;

/**
 * DEPRECATED - please use HttpExceptionMiddleware, already json aware
 */
class JsonExceptionMiddleware extends AbstractMiddleware
{
    public function __invoke(
        Request $request,
        RequestHandler $handler
    ): Response {
        $this->logger->warn(__CLASS__ . "is deprecated, use HttpExceptionMiddleware instead");

        try {
            return $handler->handle($request);
        } catch (HttpException $httpException) {
            $this->logger->debug([
                $httpException->getCode(), $httpException->getMessage()
            ]);

            return HttpRequest::jsonRpcResponse(["error" => [
                'code' => -32603,
                'message' => sprintf(
                    "%s %s",
                    $httpException->getCode(),
                    $httpException->getMessage()
                )
            ]]);
        }
    }
}
