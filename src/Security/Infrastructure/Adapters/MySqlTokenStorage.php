<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Shared\ValueObject\Token;
use App\Security\Domain\Shared\ValueObject\TokenType;
use Doctrine\DBAL\Connection;

final class MySqlTokenStorage
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function saveToken(Token $token): void
    {
        $this->connection->insert(
            'security_tokens',
            [
                'value' => $token->getValue(),
                'created_at' => $token->getCreatedAt(),
                'expire_at' => $token->getExpirationDate(),
                'type' => $token->getTokenType()->name,
                'user_id' => $token->getUserId(),
            ],
            [
                'created_at' => 'datetime',
                'expire_at' => 'datetime',
            ]
        );
    }

    public function removeToken(string $userId, TokenType $tokenType): void
    {
        $this->connection->delete(
            'security_tokens',
            [
                'user_id' => $userId,
                'type' => $tokenType->name,
            ]
        );
    }
}
