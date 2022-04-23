<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\User;
use App\Security\Domain\Ports\IProvideUsers;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\TokenType;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

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
    public function getUserToActivate(Uuid $userId): User
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

        $userId = Uuid::fromString($userData['id']);

        $token = $this->tokenStorage->getToken($userId, TokenType::AUTHENTICATION);

        return new User(
            $userId,
            $email,
            new Password($userData['password']),
            (bool) $userData['is_active'],
            $token
        );
    }

    public function getForgottenPasswordUser(Uuid $userId): User
    {
        return $this->getUserByIdAndTokenType($userId, TokenType::FORGOTTEN_PASSWORD);
    }

    private function getUserByIdAndTokenType(Uuid $userId, TokenType $tokenType): User
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
