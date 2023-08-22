<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class HttpRequest
{
    /**
     * Check if request require json response
     */
    public static function asJson(Request $request): bool
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

    public static function jsonRpcResponse(
        array $variables = [],
        ?int $id = null
    ): ResponseInterface {
        try {
            if (!empty($variables["error"])) {
                $payload =  [
                    'jsonrpc' => '2.0',
                    'id' => (is_null($id) ? time() : $id),
                    'error' => $variables["error"]
                ];
            } else {
                $payload = [
                    'jsonrpc' => '2.0',
                    'id' => (is_null($id) ? time() : $id),
                    'result' => (empty($variables["result"]) ? $variables : $variables["result"]),
                ];
            }
        } catch (Exception $e) {
            $payload = json_encode([
                'jsonrpc' => '2.0',
                'id' => (is_null($id) ? time() : $id),
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
            ->withStatus(200);
    }
}
