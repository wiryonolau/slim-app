<?php
declare(strict_types = 1);

namespace Itseasy\Action;

use Itseasy\View\ViewInterface;
use Laminas\Log\LoggerAwareTrait;
use Laminas\Log\LoggerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\EventManagerAwareInterface;

class BaseAction implements LoggerAwareInterface, EventManagerAwareInterface
{
    use LoggerAwareTrait;
    use EventManagerAwareTrait;

    protected $view = null;
    protected $logger = null;
    protected $eventManager = null;

    public function setView(ViewInterface $view) : void
    {
        $this->view = $view;
    }
}
