<?php

declare(strict_types=1);

namespace App\Security\Domain\Ports;

use App\Security\Domain\ValueObject\Token;
use App\Security\Domain\ValueObject\TokenType;
use Symfony\Component\Uid\Uuid;

interface IGenerateTokens
{
    public function generate(Uuid $userId, TokenType $tokenType, \DateTimeImmutable $occurredOn): Token;
}
