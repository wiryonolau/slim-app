<?php

namespace Itseasy\Guard;

interface IdentityInterface {
    public function getRoles() : array;
}
