<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Registration\Model\RegisteredUser;
use App\Security\Domain\Registration\Model\UserToRegister;
use App\Security\Domain\Registration\Ports\IStoreRegisteredUsers;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\TokenType;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

final class MySqlRegisteredUserStorage implements IStoreRegisteredUsers
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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

    public function create(UserToRegister $user): void
    {
        $now = new \DateTimeImmutable();

        $this->connection->beginTransaction();
        try {
            $this->connection->insert(
                'security_users',
                [
                    'id' => $user->getUuid()->jsonSerialize(),
                    'email' => $user->getEmail()->value(),
                    'password' => $user->getPassword(),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'is_active' => $user->isActive()
                ],
                [
                    'created_at' => 'datetime',
                    'updated_at' => 'datetime',
                    'is_active' => 'boolean',
                ]
            );

            $token = $user->getActivationToken();

            $this->connection->insert(
                'security_tokens',
                [
                    'value' => $token->getValue(),
                    'created_at' => $token->getCreatedAt(),
                    'expire_at' => $token->getExpirationDate(),
                    'type' => $token->getTokenType()->name,
                    'user_id' => $user->getUuid()->jsonSerialize(),
                ],
                [
                    'created_at' => 'datetime',
                    'expire_at' => 'datetime',
                ]
            );

            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        }
    }

    public function get(Uuid $uuid): RegisteredUser
    {
        $result = $this->connection->createQueryBuilder()
            ->select('security_users.email, security_tokens.value')
            ->from('security_users')
            ->join('security_users', 'security_tokens', 'security_tokens', 'security_users.id = security_tokens.user_id')
            ->where('id = :id')
            ->andWhere('security_tokens.type = :type')
            ->setParameter('id', $uuid->jsonSerialize())
            ->setParameter('type', TokenType::ACTIVATION->name)
            ->fetchAssociative();

        if ($result === false) {
            throw new UserNotFound();
        }

        return new RegisteredUser(
            $result['email'],
            $result['value'],
        );
    }
}
