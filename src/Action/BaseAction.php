<?php
declare(strict_types = 1);

namespace Itseasy\Action;

use Itseasy\View\ViewInterface;

class BaseAction
{
    protected $view = null;

    public function setView(ViewInterface $view) : void
    {
        $this->view = $view;
    }
}
