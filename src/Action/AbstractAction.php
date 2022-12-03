<?php

declare(strict_types=1);

namespace Itseasy\Action;

use Itseasy\View\ViewInterface;
use Laminas\Log\LoggerAwareTrait;
use Laminas\Log\LoggerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\EventManagerAwareInterface;
use Itseasy\Identity\IdentityAwareTrait;
use Itseasy\Identity\IdentityAwareInterface;

abstract class AbstractAction implements
    LoggerAwareInterface,
    EventManagerAwareInterface,
    IdentityAwareInterface
{
    use LoggerAwareTrait;
    use EventManagerAwareTrait;
    use IdentityAwareTrait;

    protected $view = null;
    protected $logger = null;
    protected $eventManager = null;

    public function setView(ViewInterface $view): void
    {
        $this->view = $view;
    }
}
