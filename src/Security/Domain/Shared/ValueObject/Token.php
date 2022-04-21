<?php
declare(strict_types=1);

namespace App\Security\Domain\Shared\ValueObject;

final class Token
{
    private string $value;
    private \DateInterval $duration;
    private \DateTimeImmutable $createdAt;
    private TokenType $tokenType;

    public function __construct(
        string $value,
        \DateTimeImmutable $createdAt,
        TokenType $tokenType
    ) {
        $this->value = $value;
        $this->duration = match ($tokenType) {
            default => \DateInterval::createFromDateString('1 day'),
            TokenType::AUTHENTICATION => \DateInterval::createFromDateString('15 day'),
        };
        $this->createdAt = $createdAt;
        $this->tokenType = $tokenType;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getExpirationDate(): \DateTimeImmutable
    {
        return $this->getCreatedAt()->add($this->duration);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getTokenType(): TokenType
    {
        return $this->tokenType;
    }

    public function isExpired(): bool
    {
        return $this->getExpirationDate() < new \DateTimeImmutable();
    }
}
