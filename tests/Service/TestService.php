<?php

namespace Itseasy\Test\Service;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;

class TestService implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    public function run()
    {
        $this->getEventManager()->trigger('do', null, ["call by event"]);
    }
}
