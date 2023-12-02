<?php

declare(strict_types=1);

namespace Itseasy\Action;

use Exception;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Itseasy\Http\HttpRequest;
use Itseasy\Stdlib\ArrayUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;

class InvokableAction extends AbstractAction
{
    protected $request = null;
    protected $response = null;
    protected $parsedBody = [];
    protected $arguments = [];

    // Allow method fallback from json to normal method if not exist
    // e.g httpGetJson to httpGet
    protected $jsonFallback = true;

    private $isJsonRequest = false;

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
            RequestMethodInterface::METHOD_GET,
            RequestMethodInterface::METHOD_HEAD,
            RequestMethodInterface::METHOD_POST,
            RequestMethodInterface::METHOD_PUT,
            RequestMethodInterface::METHOD_DELETE,
            RequestMethodInterface::METHOD_CONNECT,
            RequestMethodInterface::METHOD_OPTIONS,
            RequestMethodInterface::METHOD_TRACE,
            RequestMethodInterface::METHOD_PATCH
        ])) {
            throw new HttpMethodNotAllowedException($this->request);
        }

        $this->parseRequest();

        $this->isJsonRequest = HttpRequest::asJson($this->request);

        $method = strtolower($method);
        $action_method = ucfirst($method);
        $http_function_name = sprintf("%s%s", "http", $action_method);
        $json_function_name = sprintf("%s%s%s", "http", $action_method, "Json");

        $this->getEventManager()->trigger(
            sprintf('action.%s.pre', $method),
            null,
            [
                "request" => $this->request,
                "arguments" => $this->arguments,
                "parsedBody" => $this->parsedBody
            ]
        );

        // call function that ask for json response , method name is httpGetJson, httpPostJson, etc
        // function must return array or implement getArrayCopy
        // response will be return as json directly
        // if not exists fallback to normat httpGet, httpPost funciton
        if ($this->asJson() and method_exists($this, $json_function_name)) {
            $actionResponse = call_user_func_array(
                [$this, $http_function_name],
                []
            );
            if (is_array($actionResponse)) {
                return $this->renderAsJson($actionResponse);
            }

            if (method_exists($actionResponse, "getArrayCopy")) {
                return $this->renderAsJson($actionResponse->getArrayCopy());
            }
        }

        if ($this->asJson() and !$this->jsonFallback) {
            $this->forbidden("Action not exist");
        }

        // call function base on http method e.g httpGet, httpPost, etc
        if (!method_exists($this, $http_function_name)) {
            $this->forbidden("Action not exist");
        }

        $actionResponse = call_user_func_array([$this, $http_function_name], []);

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
        ?int $id = null,
        ?int $http_status_code = null
    ): ResponseInterface {
        return HttpRequest::jsonRpcResponse(
            $variables,
            $id,
            $http_status_code
        );
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
            $value = ArrayUtils::query(
                $this->request->getQueryParams(),
                $key,
                null
            );

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

            $value = ArrayUtils::query(
                $this->parsedBody,
                $key,
                null
            );

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

    /**
     * @see HttpRequest::eventStreamResponse
     */
    protected function eventStreamResponse(
        $function,
        $args = [],
        int $delay = 1
    ): ResponseInterface {
        return HttpRequest::eventStreamResponse(
            $function,
            $args,
            $delay
        );
    }

    protected function redirect(
        string $path,
        array $query = []
    ): ResponseInterface {
        return $this->response->withHeader(
            "Location",
            $this->view->url($path, $query)
        );
    }

    protected function errorResponse(
        string $message = "",
        int $http_status_code = StatusCodeInterface::STATUS_BAD_REQUEST
    ): void {
        throw new HttpException($this->request, $message, $http_status_code);
    }

    protected function forbidden(string $message = "Forbidden Access"): void
    {
        throw new HttpForbiddenException($this->request, $message);
    }

    protected function asJson(): bool
    {
        return $this->isJsonRequest;
    }
}
