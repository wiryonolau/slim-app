<?php

namespace Itseasy\Identity;

interface IdentityAwareInterface
{
    public function setIdentityProvider(?IdentityProviderInterface $identityProvider = null);
    public function getIdentity() : ?IdentityInterface;
}
