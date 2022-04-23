<?php

namespace App\Security\Domain\Ports;

use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\ValueObject\Token;
use App\Security\Domain\ValueObject\TokenType;
use Symfony\Component\Uid\Uuid;

interface IProvideTokens
{
    /** @throws TokenNotFound */
    public function getToken(Uuid $userId, TokenType $tokenType): Token;

    /** @throws TokenNotFound */
    public function getTokenByValue(string $value, TokenType $tokenType): Token;
}
