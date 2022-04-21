<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\Model\AuthenticatedUser;
use App\Security\Domain\Shared\Ports\IStoreAuthenticatedUsers;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Token;
use App\Security\Domain\Shared\ValueObject\TokenType;
use Doctrine\DBAL\Connection;

final class MySqlAuthenticatedUserStorage implements IStoreAuthenticatedUsers
{
    private Connection $connection;
    private MySqlTokenStorage $tokenStorage;

    public function __construct(Connection $connection, MySqlTokenStorage $tokenStorage)
    {
        $this->connection = $connection;
        $this->tokenStorage = $tokenStorage;
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

    public function renewAuthenticationToken(Token $authenticationToken): void
    {
        $this->tokenStorage->removeToken($authenticationToken->getUserId(), TokenType::AUTHENTICATION);
        $this->tokenStorage->saveToken($authenticationToken);
    }
}
