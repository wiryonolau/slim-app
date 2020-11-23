<?php

namespace App\Action;

use App\View\ViewInterface;

class BaseAction {
    protected $view;

    public function setView(ViewInterface $view) {
        $this->view = $view;
    }
}
