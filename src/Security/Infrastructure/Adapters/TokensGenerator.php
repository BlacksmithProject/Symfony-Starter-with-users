<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Ports\IGenerateTokens;
use App\Security\Domain\ValueObject\Token;
use App\Security\Domain\ValueObject\TokenType;
use Symfony\Component\Uid\Uuid;

final class TokensGenerator implements IGenerateTokens
{
    public function generate(Uuid $userId, TokenType $tokenType): Token
    {
        $duration = match ($tokenType) {
            default => \DateInterval::createFromDateString('1 day'),
            TokenType::AUTHENTICATION => \DateInterval::createFromDateString('15 day'),
        };

        $now = new \DateTimeImmutable();
        $expirationDate = $now->add($duration);

        return new Token($userId, $this->generateValue(), $now, $expirationDate, $tokenType);
    }

    private function generateValue(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
