<?php

declare(strict_types=1);

namespace App\Security\Domain\Ports;

use App\Security\Domain\ValueObject\Identity;
use App\Security\Domain\ValueObject\Token;
use App\Security\Domain\ValueObject\TokenType;

interface IGenerateTokens
{
    public function generate(Identity $userId, TokenType $tokenType, \DateTimeImmutable $occurredOn): Token;
}
