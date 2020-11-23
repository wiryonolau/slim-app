<?php

namespace App\View\Helper;

class UrlHelper implements ViewHelperInterface {
    public function __invoke() {
        return $this;
    }

    public function getName():string {
        return "url";
    }

    public function build($name) {
        return $name;
    }
}

