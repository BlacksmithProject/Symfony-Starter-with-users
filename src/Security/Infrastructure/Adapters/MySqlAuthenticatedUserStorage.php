<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Authentication\Model\AuthenticatedUser;
use App\Security\Domain\Authentication\Ports\IStoreAuthenticatedUsers;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Token;
use App\Security\Domain\Shared\ValueObject\TokenType;
use Doctrine\DBAL\Connection;

final class MySqlAuthenticatedUserStorage implements IStoreAuthenticatedUsers
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getByEmail(Email $email): AuthenticatedUser
    {
        $result = $this->connection->createQueryBuilder()
            ->select('security_users.id, security_users.email, security_users.password, security_tokens.value, security_tokens.expire_at')
            ->from('security_users')
            ->join('security_users', 'security_tokens', 'security_tokens', 'security_users.id = security_tokens.user_id')
            ->where('security_users.email = :email')
            ->andWhere('security_users.is_active = :isActive')
            ->andWhere('security_tokens.type = :type')
            ->setParameter('email', $email->value())
            ->setParameter('isActive', true)
            ->setParameter('type', TokenType::AUTHENTICATION->name)
            ->fetchAssociative();

        if ($result === false) {
            throw new UserNotFound();
        }

        return new AuthenticatedUser(
            $result['id'],
            $result['email'],
            $result['password'],
            $result['value'],
            new \DateTimeImmutable($result['expire_at'])
        );
    }

    public function renewAuthenticationToken(string $userId, Token $authenticationToken): void
    {
        $this->connection->delete(
            'security_tokens',
            [
                'user_id' => $userId,
                'type' => TokenType::AUTHENTICATION->name,
            ]
        );
        $this->connection->insert(
            'security_tokens',
            [
                'value' => $authenticationToken->getValue(),
                'created_at' => $authenticationToken->getCreatedAt(),
                'expire_at' => $authenticationToken->getExpirationDate(),
                'type' => $authenticationToken->getTokenType()->name,
                'user_id' => $userId,
            ],
            [
                'created_at' => 'datetime',
                'expire_at' => 'datetime',
            ]
        );
    }
}
