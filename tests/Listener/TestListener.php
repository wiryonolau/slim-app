<?php

namespace Itseasy\Test\Listener;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;

class TestListener implements ListenerAggregateInterface, LoggerAwareInterface
{
    use ListenerAggregateTrait;
    use LoggerAwareTrait;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach('do', [$this, 'log']);
        $this->listeners[] = $events->attach('doSomethingElse', [$this, 'log']);
    }

    public function log(EventInterface $e)
    {
        $event  = $e->getName();
        $params = $e->getParams();
        $this->logger->info(sprintf('%s: %s', $event, json_encode($params)));
    }
}
