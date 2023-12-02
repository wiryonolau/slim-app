<?php

declare(strict_types=1);

namespace Itseasy\Http;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\NonBufferedBody;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Throwable;

class HttpRequest
{
    /**
     * Check if request require json response
     */
    public static function asJson(Request $request): bool
    {
        try {
            if ($request->getHeaderLine('Content-Type') === "application/json") {
                return true;
            }

            $queries = $request->getQueryParams();

            if (isset($queries["format"]) and $queries["format"] == "json") {
                return true;
            }

            if (isset($queries["output"]) and $queries["output"] == "json") {
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

    public static function jsonRpcResponse(
        array $variables = [],
        ?int $id = null,
        ?int $http_status_code = null
    ): ResponseInterface {
        try {
            if (!empty($variables["error"])) {
                $payload =  [
                    'jsonrpc' => '2.0',
                    'id' => $id ?: time(),
                    'error' => $variables["error"]
                ];
            } else {
                $payload = [
                    'jsonrpc' => '2.0',
                    'id' => $id ?: time(),
                    'result' => (empty($variables["result"]) ? $variables : $variables["result"]),
                ];
            }
        } catch (Exception $e) {
            $payload = json_encode([
                'jsonrpc' => '2.0',
                'id' => $id ?: time(),
                'error' => [
                    'code' => -32603,
                    'message' => $e->getMessage(),
                ],
            ]);
        }

        $response = new Response();
        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(empty($http_status_code) ? StatusCodeInterface::STATUS_OK : $http_status_code);
    }

    /**
     * SSE Implementation
     * If use nested loop must check itself if connection is aborted
     * response body will be append to args as first argument
     * 
     * @param array $function function to run to produce message
     * @param array $args function arguments
     * @param int $delay loop delay
     */
    public static function eventStreamResponse(
        $function,
        array $args = [],
        int $delay = 1
    ): ResponseInterface {
        $response = new Response(
            StatusCodeInterface::STATUS_OK,
            new Headers([
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no'
            ]),
            new NonBufferedBody()
        );

        $body = $response->getBody();
        array_unshift($args, $body);

        try {
            while (true) {
                $message = call_user_func_array($function, $args);

                if (!empty($message)) {
                    $body->write($message . ' ');
                }

                if (connection_aborted()) {
                    throw new Exception("Connection aborted");
                }

                sleep($delay);
            }
        } catch (Throwable $t) {
        }

        return $response;
    }
}
