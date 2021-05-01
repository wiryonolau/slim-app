<?php
declare(strict_types = 1);

namespace Itseasy\View;

use Laminas\Stdlib\ArrayUtils;
use Psr\Http\Message\ResponseInterface as Response;

class View implements ViewInterface
{
    protected $renderer;
    protected $scripts = [];
    protected $variables = [];

    public function setRenderer($renderer) : void
    {
        $this->renderer = $renderer;
    }

    // Call registered ViewHelper in the renderer
    public function __call($function, $args)
    {
        return call_user_func_array([$this->renderer, $function], $args);
    }

    public function setLayout(string $layout) : void
    {
        $this->renderer->setLayout($layout);
    }

    public function setVariable($key, $value, $for_layout = false) : void
    {
        if ($for_layout) {
            if (!isset($this->variables["layout"])) {
                $this->variables["layout"] = [];
            }
            $this->variables["layout"][$key] = $value;
        } else {
            $this->variables[$key] = $value;
        }
    }

    public function appendScript($type, $path, array $options = []) : void
    {
        if (in_array($type, ["js", "css"])) {
            $path = ltrim($path, "/");

            $script = new \StdClass();
            $script->type = $type;
            $script->path = sprintf("/%s", $path);
            $script->options = $options;
            $this->scripts[] = $script;
        }
    }

    public function appendScripts(array $scripts = []) : void
    {
        foreach ($scripts as $script) {
            if (count($script) == 2) {
                list($type, $path) = $script;
                $options = [];
            } elseif (count($script) == 3) {
                list($type, $path, $options) = $script;
            } else {
                continue;
            }
            $this->appendScript($type, $path, $options);
        }
    }

    public function render(Response $response, string $template, array $variables = [], string $layout = "") : Response
    {
        $variables = ArrayUtils::merge($this->variables, $variables);

        if (empty($variables["layout"])) {
            $variables["layout"] = [];
        }
        $variables["layout"]["scripts"] = $this->scripts;

        return $this->renderer->render($response, $template, $variables);
    }
}
