<?php

declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Model\User;
use App\Security\Domain\Ports\IStoreUsers;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\TokenType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final class MySqlUserStorage implements IStoreUsers
{
    private Connection $connection;
    private MySqlTokenStorage $tokenStorage;

    public function __construct(
        Connection $connection,
        MySqlTokenStorage $tokenStorage
    ) {
        $this->connection = $connection;
        $this->tokenStorage = $tokenStorage;
    }

    public function isEmailAlreadyUsed(Email $email): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('security_users')
            ->where('email = :email')
            ->setParameter('email', $email->value())
            ->fetchOne();

        return $result !== 0;
    }

    public function add(User $user): void
    {
        $now = new \DateTimeImmutable();

        $this->connection->beginTransaction();
        try {
            $this->saveUser($user, $now);
            $this->tokenStorage->saveToken($user->getToken());

            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        }
    }

    public function activate(User $user): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->update(
                'security_users',
                [
                    'is_active' => true,
                    'updated_at' => new \DateTimeImmutable(),
                ],
                [
                    'id' => $user->getId(),
                ],
                [
                    'is_active' => 'boolean',
                    'updated_at' => 'datetime',
                ]
            );

            $this->tokenStorage->removeToken($user->getId(), TokenType::ACTIVATION);
            $this->tokenStorage->saveToken($user->getToken());

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    public function renewForgottenPassword(User $user): void
    {
        $this->tokenStorage->removeToken($user->getId(), TokenType::FORGOTTEN_PASSWORD);
        $this->tokenStorage->saveToken($user->getToken());
    }

    public function updatePassword(User $user): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->update(
                'security_users',
                [
                    'password' => $user->getPassword()->value(),
                    'updated_at' => new \DateTimeImmutable(),
                ],
                [
                    'id' => $user->getId(),
                ],
                [
                    'updated_at' => 'datetime',
                ]
            );

            $this->tokenStorage->removeToken($user->getId(), TokenType::FORGOTTEN_PASSWORD);
            $this->tokenStorage->renew($user->getToken());

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    private function saveUser(User $user, \DateTimeImmutable $now): void
    {
        $this->connection->insert(
            'security_users',
            [
                'id' => $user->getId()->jsonSerialize(),
                'email' => $user->getEmail()->value(),
                'password' => $user->getPassword()->value(),
                'created_at' => $now,
                'updated_at' => $now,
                'is_active' => $user->isActive(),
            ],
            [
                'created_at' => 'datetime',
                'updated_at' => 'datetime',
                'is_active' => 'boolean',
            ]
        );
    }
}
