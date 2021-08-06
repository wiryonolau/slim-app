<?php

namespace Itseasy\Guard;

trait IdentityAwareTrait
{
    protected $identityProvider = null;

    public function setIdentityProvider(?IdentityProviderInterface $identityProvider = null) : void
    {
        $this->identityProvider = $identityProvider;
    }

    public function getIdentity() : ?IdentityInterface
    {
        if (is_null($this->identityProvider)) {
            return null;
        }
        return $this->identityProvider->getIdentity();
    }
}
