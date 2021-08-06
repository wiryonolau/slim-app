<?php

namespace Itseasy\Guard;

interface IdentityAwareInterface
{
    public function setIdentityProvider(?IdentityProviderInterface $identityProvider = null);
    public function getIdentity() : ?IdentityInterface;
}
