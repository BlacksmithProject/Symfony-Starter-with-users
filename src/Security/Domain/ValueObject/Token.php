<?php

declare(strict_types=1);

namespace App\Security\Domain\ValueObject;

final class Token
{
    private Identity $userId;
    private string $value;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $expiredAt;
    private TokenType $tokenType;

    public function __construct(
        Identity $userId,
        string $value,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $expiredAt,
        TokenType $tokenType,
    ) {
        $this->userId = $userId;
        $this->value = $value;
        $this->createdAt = $createdAt;
        $this->expiredAt = $expiredAt;
        $this->tokenType = $tokenType;
    }

    public function getUserId(): Identity
    {
        return $this->userId;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getExpiredAt(): \DateTimeImmutable
    {
        return $this->expiredAt;
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
        return $this->expiredAt < new \DateTimeImmutable();
    }
}
