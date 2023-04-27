<?php

namespace App\Security\Domain\Ports;

use App\Security\Domain\ValueObject\Token;

interface IStoreTokens
{
    public function renew(Token $token): void;
}
