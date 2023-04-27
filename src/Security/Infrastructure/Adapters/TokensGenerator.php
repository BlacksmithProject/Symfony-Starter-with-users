<?php

declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Ports\IGenerateTokens;
use App\Security\Domain\ValueObject\Identity;
use App\Security\Domain\ValueObject\Token;
use App\Security\Domain\ValueObject\TokenType;

final class TokensGenerator implements IGenerateTokens
{
    public function generate(Identity $userId, TokenType $tokenType, \DateTimeImmutable $occurredOn): Token
    {
        $duration = match ($tokenType) {
            default => new \DateInterval('P1D'),
            TokenType::AUTHENTICATION => new \DateInterval('P15D'),
        };

        $expirationDate = $occurredOn->add($duration);

        return new Token($userId, $this->generateValue(), $occurredOn, $expirationDate, $tokenType);
    }

    private function generateValue(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
