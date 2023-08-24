<?php

declare(strict_types=1);

namespace Itseasy\Action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpForbiddenException;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Itseasy\Http\HttpRequest;
use Slim\Exception\HttpException;

class InvokableAction extends AbstractAction
{
    protected $request = null;
    protected $response = null;
    protected $parsedBody = [];
    protected $arguments = [];

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $arguments = []
    ): ResponseInterface {
        $this->request = $request;
        $this->response = $response;
        $this->arguments = $arguments;
        $this->parsedBody = (array)$this->request->getParsedBody();

        $method = $this->request->getMethod();

        if (!in_array($method, [
            "GET", "HEAD", "POST", "PUT", "DELETE", "CONNECT", "OPTIONS", "TRACE", "PATCH"
        ])) {
            throw new Exception("Invalid HTTP Method");
        }

        $this->parseRequest();

        $method = strtolower($method);
        $function_name = sprintf("%s%s", "http", ucfirst($method));

        // call function base on http method e.g httpGet, httpPost, etc
        if (method_exists($this, $function_name)) {
            $this->getEventManager()->trigger(
                sprintf('action.%s.pre', $method),
                null,
                [
                    "request" => $this->request,
                    "arguments" => $this->arguments,
                    "parsedBody" => $this->parsedBody
                ]
            );

            $actionResponse = call_user_func_array([$this, $function_name], []);

            $this->getEventManager()->trigger(
                sprintf('action.%s.post', $method),
                null,
                [
                    "request" => $this->request,
                    "arguments" => $this->arguments,
                    "parsedBody" => $this->parsedBody
                ]
            );

            return $actionResponse;
        }

        return $this->response;
    }

    protected function render(
        string $template,
        array $variables = [],
        string $layout = ""
    ): ResponseInterface {
        if (!is_null($this->view)) {
            return $this->view->render(
                $this->response,
                $template,
                $variables,
                $layout
            );
        }
        return $this->response;
    }

    /**
     * Return json directly without rendering layout
     * if $variables["error"] is define will render error instead of result
     * if $variables["result"] is define variables result key will be ommited
     */
    public function renderAsJson(
        array $variables = [],
        ?int $id = null
    ): ResponseInterface {
        return HttpRequest::jsonRpcResponse($variables, $id);
    }


    protected function parseRequest(): void
    {
        // overload this function to parse request during invoke
    }

    /**
     * @return string|array|null
     */
    protected function getQuery(
        string $key,
        $placeholder = null,
        $ignore_empty = false
    ) {
        try {
            $value = $this->request->getQueryParams()[$key] ?? null;
            if ($ignore_empty) {
                return $value;
            }

            if (empty($value)) {
                throw new Exception("Empty Value");
            }
            return $value;
        } catch (Exception $e) {
            return $placeholder;
        }
    }

    /**
     * @return string|array|null
     */
    protected function getPost(
        string $key,
        $placeholder = null,
        $ignore_empty = false
    ) {
        return $this->getParsedBody($key, $placeholder, $ignore_empty);
    }

    /**
     * @return string|array|null
     */
    protected function getParsedBody(
        string $key,
        $placeholder = null,
        $ignore_empty = false
    ) {
        try {
            if (!in_array($this->request->getMethod(), ["POST", "PUT"])) {
                throw new Exception("Request is not a POST or PUT");
            }
            $value = $this->parsedBody[$key] ?? null;

            if ($ignore_empty) {
                return $value;
            }

            if (empty($value)) {
                throw new Exception("Empty Value");
            }
            return $value;
        } catch (Exception $e) {
            return $placeholder;
        }
    }

    /**
     * @return string|array|null
     */
    protected function getArgument(
        string $key,
        $placeholder = null,
        $ignore_empty = false
    ) {
        try {
            $value = $this->arguments[$key] ?? null;
            if ($ignore_empty) {
                return $value;
            }

            if (empty($value)) {
                throw new Exception("Empty Value");
            }
            return $value;
        } catch (Exception $e) {
            return $placeholder;
        }
    }

    protected function redirect(string $path, array $query = []): ResponseInterface
    {
        return $this->response->withHeader(
            "Location",
            $this->view->url($path, $query)
        );
    }

    protected function errorResponse(
        string $message = "",
        int $http_status_code = StatusCodeInterface::STATUS_OK
    ): void {
        throw new HttpException($this->request, $message, $http_status_code);
    }

    protected function forbidden(string $message = ""): void
    {
        throw new HttpForbiddenException($this->request, $message);
    }

    protected function asJson(): bool
    {
        return HttpRequest::asJson($this->request);
    }
}
