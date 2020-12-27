<?php
namespace Itseasy\Csrf;

use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager as SymfonyCsrfTokenManager;

class CsrfTokenManager extends SymfonyCsrfTokenManager {
    protected $token_id;

    public function __construct(string $token_id,
            TokenGeneratorInterface $generator,
            TokenStorageInterface $storage,
            string $namespace = ""
        ) {
        parent::__construct($generator, $storage, $namespace);

        $this->token_id = $token_id;
    }


    public function getId() : string {
        return $this->token_id;
    }

    public function getToken(string $token_id = "")
    {
        $token_id = ( !empty($token_id) ? : $this->token_id );
        return parent::getToken($token_id);
    }

    public function refreshToken(string $token_id = "")
    {
        $token_id = ( !empty($token_id) ? : $this->token_id );
        return parent::refreshToken($token_id);
    }

    public function removeToken(string $token_id = "")
    {
        $token_id = ( !empty($token_id) ? : $this->token_id );
        return parent::removeToken($token_id);
    }
}
