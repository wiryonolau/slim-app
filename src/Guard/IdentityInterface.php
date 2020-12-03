<?php

namespace App\Guard;

interface IdentityInterface {
    public function getRoles() : array;
}
