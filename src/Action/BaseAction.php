<?php

namespace App\Action;

class BaseAction {
    protected $renderer;

    public function setRenderer($renderer) {
        $this->renderer = $renderer;
    }

    protected function render($response, $template, $variables) {
        $template = sprintf("%s.phtml", $template);
        return $this->renderer->render($response, $template, $variables);
    }
}
