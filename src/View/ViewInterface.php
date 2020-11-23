<?php

namespace App\View;

interface ViewInterface {
    public function setRenderer($renderer);
    public function setLayout($layout);
    public function render($response, $template, $variables);
}
