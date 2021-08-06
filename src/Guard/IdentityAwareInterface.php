<?php

namespace Itseasy\Guard;

use IdentityInterface;

interface IdentityAwareInterface
{
    public function setIdentity(IdentityInterface $identity);
}
