<?php

namespace Itseasy\Action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpForbiddenException;
use Exception;

class InvokableAction extends BaseAction {
    protected $request = null;
    protected $response = null;
    protected $arguments = [];

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $arguments = []) : ResponseInterface {
        $this->request = $request;
        $this->response = $response;
        $this->arguments = $arguments;

        $method = $this->request->getMethod();

        if (!in_array($method, ["GET", "HEAD", "POST","PUT", "DELETE", "CONNECT","OPTIONS","TRACE","PATCH"])) {
            throw new Exception("Invalid HTTP Method");
        }

        $this->parseRequest();

        $function_name = sprintf("%s%s", "http", ucfirst(strtolower($method)));

        // call function base on http method e.g httpGet, httpPost, etc
        if (method_exists($this, $function_name)) {
            return call_user_func_array([$this, $function_name], []);
        }

        return $this->response;
    }

    protected function render(string $template, array $variables = [], string $layout = "") : ResponseInterface {
        if (!is_null($this->view)) {
            return $this->view->render($this->response, $template, $variables, $layout);
        }
        return $this->response;
    }

    protected function parseRequest() {
        // overload this function to parse request during invoke
    }

    protected function getQuery(string $key, $placeholder = null, $ignore_empty = false) {
        try {
            $value = $this->request->getQueryParams()[$key];
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

    protected function getPost(string $key, $placeholder = null, $ignore_empty = false) {
        try {
            if ($this->request->getMethod() != "POST") {
                throw new Exception("Request is not a POST");
            }
            $value = $request->getParsedBody()[$key];

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

    protected function getArgument(string $key, $placeholder = null, $ignore_empty = false) {
        try {
            $value = $this->arguments[$key];
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

    protected function redirect(string $path, array $query = []) : ResponseInterface {
        return $this->response->withHeader("Location", $this->view->url($path, $query));
    }

    protected function forbidden(string $message = "") {
        throw new HttpForbiddenException($this->request, $message);
    }

    protected function asJson() {
        try {
            $format = $this->getQuery("format", "html");
            return ($format == "json");
        } catch (Exception $e) {
            return false;
        }
    }
}
