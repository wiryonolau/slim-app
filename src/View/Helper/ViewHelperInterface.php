<?php

namespace App\View\Helper;

interface ViewHelperInterface {
    public function __invoke();
    public function getName():string;
}
