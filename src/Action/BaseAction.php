<?php

namespace Itseasy\Action;

use Itseasy\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Exception;

class BaseAction {
    protected $view = null;
    protected $request = null;
    protected $response = null;
    protected $arguments = [];

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $arguments = []) : ResponseInterface {
        return $this->process($request, $response, $args);
    }

    public function setView(ViewInterface $view) {
        $this->view = $view;
    }

    protected function process(ServerRequestInterface $request, ResponseInterface $response, array $arguments = []) : ResponseInterface {
        $this->request = $request;
        $this->response = $response;
        $this->arguments = $arguments;

        $method = $this->request->getMethod();

        if (!in_array($method, ["GET", "HEAD", "POST","PUT", "DELETE", "CONNECT","OPTIONS","TRACE","PATCH"])) {
            throw new Exception("Invalid HTTP Method");
        }

        $function_name = sprintf("%s%s", "http", ucfirst(strtolower($method)));

        if (method_exists($this, $function_name)) {
            return call_user_func_array([$this, $function_name], []);
        }

        return $this->response;
    }

    protected function getQuery(string $key, $placeholder = null) {
        try {
            return $this->request->getQueryParams()[$key];
        } catch (Exception $e) {
            return $placeholder;
        }
    }

    protected function getPost(string $key, $placeholder = null) {
        try {
            if ($this->request->getMethod() != "POST") {
                return $placeholder;
            }
            return $request->getParsedBody()[$key];
        } catch (Exception $e) {
            return $placeholder;
        }
    }

    protected function getArgument(string $key, $placeholder = null) {
        try {
            return $this->arguments[$key];
        } catch (Exception $e) {
            return $placeholder;
        }
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
