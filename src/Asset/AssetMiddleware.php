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
        'js' => 'application/javascript',
        'sass' => 'text/css',
        'scss' => 'text/css',

        // text
        'eot' => 'application/vnd.ms-fontobject',
        'otf' => 'font/otf',
        'ttf' => 'font/ttf',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',

        // images
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'ico' => 'image/vnd.microsoft.icon',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'webp' => 'image/webp',

        // audio/video
        'flv' => 'video/x-flv',
        'mov' => 'video/quicktime',
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'swf' => 'application/x-shockwave-flash',
        'weba' => 'audio/webm',
        'webm' => 'video/webm'
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
