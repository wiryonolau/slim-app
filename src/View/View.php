<?php

namespace App\View;

class View implements ViewInterface {
    protected $renderer;
    
    public function setRenderer($renderer) {
        $this->renderer = $renderer;
    }
    
    public function setLayout($layout) {
        $layout = sprintf("%s.phtml", $layout);
        $this->renderer->setLayout($layout);
    }

    public function render($response, $template, $variables) {
        $template = sprintf("%s.phtml", $template);
        return $this->renderer->render($response, $template, $variables);
    }
}
