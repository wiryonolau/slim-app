<?php
declare(strict_types = 1);

namespace Itseasy\View\Helper;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class FlashMessageHelper
{
    const LEVEL_INFO = "info";
    const LEVEL_WARNING = "warning";
    const LEVEL_ERROR = "error";

    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function __invoke() : self
    {
        return $this;
    }

    public function getFlashBag() : FlashBagInterface
    {
        return $this->session->getFlashBag();
    }

    public function add(string $level, string $message) : void
    {
        $this->session->getFlashBag()->add($level, $message);
    }

    public function set(string $level, $message) : void
    {
        $this->session->getFlashBag()->set($level, $message);
    }

    public function get(string $level, array $placeholder = []) : array
    {
        return $this->session->getFlashBag()->get($level, $placeholder);
    }

    public function all() : array
    {
        return $this->session->getFlashBag()->all();
    }
}
