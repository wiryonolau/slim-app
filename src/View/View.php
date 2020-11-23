<?php

namespace App\View;

use Psr\Http\Message\ResponseInterface as Response;

class View implements ViewInterface {
    protected $renderer;

    public function setRenderer($renderer) {
        $this->renderer = $renderer;
    }
    
    public function setLayout(string $layout) {
        $layout = sprintf("%s.phtml", $layout);
        $this->renderer->setLayout($layout);
    }

    public function render(Response $response, string $template, array $variables = [], string $layout = "") {
        $template = sprintf("%s.phtml", $template);
        return $this->renderer->render($response, $template, $variables);
    }
}
