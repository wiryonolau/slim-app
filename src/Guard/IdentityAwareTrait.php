<?php

namespace Itseasy\Guard;

use IdentityInterface;

trait IdentityAwareTrait
{
    protected $identity;

    public function setIdentity(IdentityInterface $identity) : void
    {
        $this->identity = $identity;
    }
}
