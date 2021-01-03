<?php

namespace Itseasy\Asset;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response;
use Itseasy\Middleware\BaseMiddleware;

class AssetMiddleware extends BaseMiddleware
{
    protected $assetManager;

    protected $mimeType = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
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

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
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

        $content = $this->assetManager->getAsset($file_path);
        $response->getBody()->write($content);

        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        if (isset($this->mimeType[$extension])) {
            $response = $response->withHeader("Content-type", $this->mimeType[$extension]);
        }

        return $response;
    }
}
