<?php

declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\User;
use App\Security\Domain\Ports\IProvideUsers;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Identity;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\TokenType;
use Doctrine\DBAL\Connection;

final class MySqlUserProvider implements IProvideUsers
{
    private Connection $connection;
    private MySqlTokenStorage $tokenStorage;

    public function __construct(Connection $connection, MySqlTokenStorage $tokenStorage)
    {
        $this->connection = $connection;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @throws TokenNotFound
     * @throws UserNotFound
     */
    public function getUserToActivate(Identity $userId): User
    {
        return $this->getUserByIdAndTokenType($userId, TokenType::ACTIVATION);
    }

    public function getActivatedUser(Email $email): User
    {
        $userData = $this->connection->createQueryBuilder()
            ->select('security_users.id, security_users.password, security_users.is_active')
            ->from('security_users')
            ->where('email = :email')
            ->andWhere('is_active = true')
            ->setParameter('email', $email->value())
            ->fetchAssociative();

        if ($userData === false) {
            throw new UserNotFound();
        }

        $userId = new Identity($userData['id']);

        $token = $this->tokenStorage->getToken($userId, TokenType::AUTHENTICATION);

        return new User(
            $userId,
            $email,
            new Password($userData['password']),
            (bool) $userData['is_active'],
            $token
        );
    }

    public function getForgottenPasswordUser(Identity $userId): User
    {
        return $this->getUserByIdAndTokenType($userId, TokenType::FORGOTTEN_PASSWORD);
    }

    private function getUserByIdAndTokenType(Identity $userId, TokenType $tokenType): User
    {
        $userData = $this->connection->createQueryBuilder()
            ->select('security_users.email, security_users.password, security_users.is_active')
            ->from('security_users')
            ->where('id = :id')
            ->setParameter('id', $userId)
            ->fetchAssociative();

        if ($userData === false) {
            throw new UserNotFound();
        }

        $token = $this->tokenStorage->getToken($userId, $tokenType);

        return new User(
            $userId,
            new Email($userData['email']),
            new Password($userData['password']),
            (bool) $userData['is_active'],
            $token
        );
    }
}
