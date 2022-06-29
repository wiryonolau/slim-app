<?php
/**
 * Placeholder class
 * DO NOT USE THIS FOR IDENTITY PROVIDER.
 */

declare(strict_types=1);

namespace Itseasy\Identity;

class IdentityProvider implements IdentityProviderInterface
{
    public function hasIdentity(): bool
    {
        return true;
    }

    public function getIdentity(): IdentityInterface
    {
        return new Identity();
    }
}
