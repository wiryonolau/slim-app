<?php

namespace Itseasy\Action;

use Itseasy\View\ViewInterface;

class BaseAction {
    protected $view = null;

    public function setView(ViewInterface $view) {
        $this->view = $view;
    }
}
