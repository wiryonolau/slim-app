<?php
declare(strict_types = 1);

namespace Itseasy\Action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpForbiddenException;
use Exception;

class InvokableAction extends AbstractAction
{
    protected $request = null;
    protected $response = null;
    protected $parsedBody = [];
    protected $arguments = [];

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $arguments = []) : ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->arguments = $arguments;
        $this->parsedBody = (array)$this->request->getParsedBody();

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

    protected function render(string $template, array $variables = [], string $layout = "") : ResponseInterface
    {
        if (!is_null($this->view)) {
            return $this->view->render($this->response, $template, $variables, $layout);
        }
        return $this->response;
    }

    protected function parseRequest() : void
    {
        // overload this function to parse request during invoke
    }

    /**
      * @return string|array|null
      */
    protected function getQuery(string $key, $placeholder = null, $ignore_empty = false)
    {
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

    /**
      * @return string|array|null
      */
    protected function getPost(string $key, $placeholder = null, $ignore_empty = false)
    {
        return $this->getParsedBody($key, $placeholder, $ignore_empty);
    }

    /**
      * @return string|array|null
      */
    protected function getParsedBody(string $key, $placeholder = null, $ignore_empty = false)
    {
        try {
            if (!in_array($this->request->getMethod(), ["POST", "PUT"])) {
                throw new Exception("Request is not a POST or PUT");
            }
            $value = $this->parsedBody[$key];

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
    protected function getArgument(string $key, $placeholder = null, $ignore_empty = false)
    {
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

    protected function redirect(string $path, array $query = []) : ResponseInterface
    {
        return $this->response->withHeader("Location", $this->view->url($path, $query));
    }

    protected function forbidden(string $message = "") : void
    {
        throw new HttpForbiddenException($this->request, $message);
    }

    protected function asJson() : bool
    {
        try {
            $format = $this->getQuery("format", "html");
            return ($format == "json");
        } catch (Exception $e) {
            return false;
        }
    }
}
