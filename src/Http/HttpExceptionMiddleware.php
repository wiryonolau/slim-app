<?php

declare(strict_types=1);

namespace Itseasy\Http;

use Itseasy\Middleware\AbstractMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpException;
use Slim\Psr7\Response;

class HttpExceptionMiddleware extends AbstractMiddleware
{
    protected $view;

    public function __construct($view)
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

            $code = $httpException->getCode();
            $template = sprintf('/error/%d', $code);

            $variables = [
                'code' => $httpException->getCode(),
            ];

            return $this->view->render($response, $template, $variables);
        }
    }
}
