<?php

namespace Itseasy\Test\Service;

class TestService
{
    protected $name;
    protected $options;

    public function __construct($name, $options)
    {
        $this->name = $name;
        $this->options = $options;
    }
}
