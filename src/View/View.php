<?php

namespace App\View;

use Psr\Http\Message\ResponseInterface as Response;

class View implements ViewInterface
{
    protected $renderer;
    protected $scripts = [];

    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    public function setLayout(string $layout)
    {
        $layout = sprintf("%s.phtml", $layout);
        $this->renderer->setLayout($layout);
    }

    public function appendScript($type, $path, array $options = []):void
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

    public function appendScripts(array $scripts = []):void {
        foreach ($scripts as $script) {
            if (count($script) == 2) {
                list($type, $path) = $script;
                $options = [];
            } else if (count($script) == 3) {
                list($type, $path, $options) = $script;
            } else {
                continue;
            }
            $this->appendScript($type, $path, $options);
        }
    }

    public function render(Response $response, string $template, array $variables = [], string $layout = "")
    {
        if (empty($variables["scripts"])) {
            $variables["scripts"] = [];
        }
        $variables["scripts"] = array_merge_recursive($variables["scripts"], $this->scripts);

        $template = sprintf("%s.phtml", $template);
        return $this->renderer->render($response, $template, $variables);
    }
}

