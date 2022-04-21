<?php
declare(strict_types=1);

namespace App\Security\Domain\Shared\Ports;

use App\Security\Domain\Shared\ValueObject\Token;
use App\Security\Domain\Shared\ValueObject\TokenType;

interface IGenerateToken
{
    public function generate(string $userId, TokenType $tokenType): Token;
}
