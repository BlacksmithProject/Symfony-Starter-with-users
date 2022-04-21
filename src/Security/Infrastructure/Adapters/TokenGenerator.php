<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Shared\Ports\IGenerateToken;
use App\Security\Domain\Shared\ValueObject\Token;
use App\Security\Domain\Shared\ValueObject\TokenType;

final class TokenGenerator implements IGenerateToken
{
    public function generate(TokenType $tokenType): Token
    {
        return new Token($this->generateValue(), new \DateTimeImmutable(), $tokenType);
    }

    private function generateValue(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
