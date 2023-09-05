<?php

declare(strict_types=1);

namespace Itseasy\Http;

use Exception;
use Itseasy\Http\HttpRequest;
use Itseasy\Middleware\AbstractMiddleware;
use Itseasy\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpException;
use Slim\Psr7\Response;

class HttpExceptionMiddleware extends AbstractMiddleware
{
    protected $view;

    public function __construct(ViewInterface $view)
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
            if (HttpRequest::asJson($request)) {
                return HttpRequest::jsonRpcResponse(["error" => [
                    'code' => -32603,
                    'message' => sprintf(
                        "%s",
                        $httpException->getMessage()
                    )
                ]], null, $httpException->getCode());
            }

            $code = $httpException->getCode();
            $template = sprintf('/error/%d', $code);

            $variables = [
                'code' => $httpException->getCode(),
                'title' => $httpException->getTitle(),
                'description' => $httpException->getDescription(),
                'message' => $httpException->getMessage()
            ];

            try {
                return $this->view->render(
                    $response,
                    $template,
                    $variables,
                    "layout/error"
                );
            } catch (Exception $e) {
                $template = "/error/default";
                return $this->view->render(
                    $response,
                    $template,
                    $variables,
                    "layout/error"
                );
            }
        }
    }
}
