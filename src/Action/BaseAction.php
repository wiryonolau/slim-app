<?php

namespace Itseasy\Action;

use Itseasy\View\ViewInterface;

class BaseAction {
    protected $view;

    public function setView(ViewInterface $view) {
        $this->view = $view;
    }
}
