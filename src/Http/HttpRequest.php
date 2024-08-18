<?php

declare(strict_types=1);

namespace Itseasy\Http;

use Closure;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\NonBufferedBody;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Throwable;

class HttpRequest
{
    public static function getRequestIp(Request $request): string
    {
        $server = $request->getServerParams();
        if (!empty($server['HTTP_CLIENT_IP'])) {
            // Check for IP from shared internet
            $ip = $server['HTTP_CLIENT_IP'];
        } elseif (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            // Check for IP passed from a proxy
            $ip = $server['HTTP_X_FORWARDED_FOR'];
        } else {
            // Default fallback IP address
            $ip = $server['REMOTE_ADDR'];
        }

        // Handle multiple IPs (e.g., when multiple proxies are involved)
        if (strpos($ip, ',') !== false) {
            $ip = explode(',', $ip)[0];
        }

        return trim($ip);
    }

    public static function getRequestUserAgent(Request $request): string
    {
        $server = $request->getServerParams();
        return $server["HTTP_USER_AGENT"];
    }

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
     * @param Closure $function function to run to produce message
     * @param int $delay loop delay
     * 
     * @throws Exception
     * 
     * @example 
     * HttpRequest::eventStreamResponse(
     *  function(StreamInterface $body) : ?EventStreamMessage {
     *   return new EventStreamMessage("event", ["value" => 1]);
     *  }
     * )
     * 
     * HttpRequest::eventStreamResponse(
     *  function(StreamInterface $body) : ?EventStreamMessage {
     *    $message = new EventStreamMessage("ping", ["time" => 1]);
     *    $body->write($message->getMessage());
     *    return;
     *  }
     * )
     */
    public static function eventStreamResponse(
        Closure $function,
        int $delay = 1
    ): ResponseInterface {
        $response = new Response();
        $response = $response->withBody(new NonBufferedBody())
            ->withHeader('Content-Type', 'text/event-stream')
            ->withHeader('Cache-Control', 'no-cache')
            ->withHeader('X-Accel-Buffering', 'no');

        $body = $response->getBody();

        while (true) {
            sleep($delay);

            $message = $function($body);

            if (empty($message)) {
                continue;
            }

            if (!$message instanceof EventStreamMessage) {
                throw new Exception("Message must be EventStreamMessage");
            }

            $message->writeToStream($body);

            if (connection_aborted()) {
                throw new Exception("Connection aborted");
            }
        }

        return $response;
    }
}
