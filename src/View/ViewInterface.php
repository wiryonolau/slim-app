<?php

namespace App\View;

use Psr\Http\Message\ResponseInterface as Response;

interface ViewInterface {
    public function setRenderer($renderer);
    public function setLayout(string $layout);
    public function render(Response $response, string $template, array $variables = [], string $layout = "");
}
