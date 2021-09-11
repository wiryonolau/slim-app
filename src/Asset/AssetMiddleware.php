<?php
declare(strict_types = 1);

namespace Itseasy\Asset;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Slim\Psr7\Response;
use Itseasy\Middleware\AbstractMiddleware;

class AssetMiddleware extends AbstractMiddleware
{
    protected $assetManager;

    protected $mimeType = [
        'css' => 'text/css',
        'sass' => 'text/css',
        'scss' => 'text/css',
        'js' => 'application/javascript',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

    ];

    public function __construct(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response
    {
        try {
            return $handler->handle($request);
        } catch (HttpNotFoundException $e) {
            return $this->findAsset($request);
        }
    }

    private function findAsset(Request $request) : Response
    {
        $response = new Response();

        $request_file = $request->getUri()->getPath();
        $file_path = $this->assetManager->getAssetRealPath($request_file);

        if (is_null($file_path)) {
            throw new HttpNotFoundException($request);
        }

        $extension = pathinfo($file_path, PATHINFO_EXTENSION);

        if (isset($this->mimeType[$extension])) {
            $content = $this->assetManager->getAsset($file_path);
            $response = $response->withHeader("Content-type", $this->mimeType[$extension]);
            $response->getBody()->write($content);
            return $response;
        }

        throw new HttpForbiddenException($request, "Unauthorized Access");
    }
}
