<?php

declare(strict_types=1);

namespace Itseasy\View\Helper;

use Itseasy\View\Renderer\PhpRenderer;

abstract class AbstractHelper
{
    protected $view;

    public function setView(PhpRenderer $view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }
}
