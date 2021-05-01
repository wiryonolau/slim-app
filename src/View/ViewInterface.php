<?php

namespace Itseasy\View;

use Psr\Http\Message\ResponseInterface as Response;

interface ViewInterface
{
    /**
     *@param object $renderer Renderer object
     */
    public function setRenderer($renderer);
    public function setLayout(string $layout) : void;
    public function render(Response $response, string $template, array $variables = [], string $layout = "") : Response;
}
