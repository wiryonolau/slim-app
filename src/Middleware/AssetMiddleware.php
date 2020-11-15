<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response;

class AssetMiddleware {
    protected $asset_path = [];

    public function __construct(array $asset_path = []) {
        $this->asset_path = $asset_path;
    }

    public function __invoke(Request $request, RequestHandler $handler,mixed $args=null) {
        try {
            $response = $handler->handle($request);
        } catch (HttpNotFoundException $e) {
            $response = $this->findAsset($request);
        }

        return $response;
    }

    private function findAsset(Request $request) {
    }
}
