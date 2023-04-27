<?php

declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\Ports\IProvideTokens;
use App\Security\Domain\Ports\IStoreTokens;
use App\Security\Domain\ValueObject\Identity;
use App\Security\Domain\ValueObject\Token;
use App\Security\Domain\ValueObject\TokenType;
use Doctrine\DBAL\Connection;

final readonly class MySqlTokenStorage implements IProvideTokens, IStoreTokens
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws TokenNotFound
     */
    public function getToken(Identity $userId, TokenType $tokenType): Token
    {
        $result = $this->connection->createQueryBuilder()
            ->select('security_tokens.*')
            ->from('security_tokens')
            ->join('security_tokens', 'security_users', 'security_users', 'security_users.id = security_tokens.user_id')
            ->where('security_users.id = :userId')
            ->andWhere('security_tokens.type = :type')
            ->setParameter('userId', $userId)
            ->setParameter('isActive', true)
            ->setParameter('type', $tokenType->value)
            ->fetchAssociative();

        if ($result === false) {
            throw new TokenNotFound();
        }

        return new Token(
            $userId,
            $result['value'],
            new \DateTimeImmutable($result['created_at']),
            new \DateTimeImmutable($result['expired_at']),
            TokenType::from($result['type'])
        );
    }

    /**
     * @throws TokenNotFound
     */
    public function getTokenByValue(string $value, TokenType $tokenType): Token
    {
        $result = $this->connection->createQueryBuilder()
            ->select('security_tokens.*, security_users.id')
            ->from('security_tokens')
            ->join('security_tokens', 'security_users', 'security_users', 'security_users.id = security_tokens.user_id')
            ->where('security_tokens.value = :value')
            ->andWhere('security_tokens.type = :type')
            ->setParameter('value', $value)
            ->setParameter('isActive', true)
            ->setParameter('type', $tokenType->value)
            ->fetchAssociative();

        if ($result === false) {
            throw new TokenNotFound();
        }

        return new Token(
            new Identity($result['id']),
            $result['value'],
            new \DateTimeImmutable($result['created_at']),
            new \DateTimeImmutable($result['expired_at']),
            TokenType::from($result['type'])
        );
    }

    public function renew(Token $token): void
    {
        $userId = $token->getUserId();
        $this->removeToken($userId, $token->getTokenType());
        $this->saveToken($token);
    }

    public function saveToken(Token $token): void
    {
        $this->connection->insert(
            'security_tokens',
            [
                'value' => $token->getValue(),
                'created_at' => $token->getCreatedAt(),
                'expired_at' => $token->getExpiredAt(),
                'type' => $token->getTokenType()->value,
                'user_id' => $token->getUserId(),
            ],
            [
                'created_at' => 'datetime',
                'expired_at' => 'datetime',
            ]
        );
    }

    public function removeToken(Identity $userId, TokenType $tokenType): void
    {
        $this->connection->delete(
            'security_tokens',
            [
                'user_id' => $userId->value,
                'type' => $tokenType->value,
            ]
        );
    }
}
