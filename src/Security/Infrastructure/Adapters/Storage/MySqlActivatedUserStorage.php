<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters\Storage;

use App\Security\Domain\Activation\Model\ActivatedUser;
use App\Security\Domain\Activation\Model\UserToActivate;
use App\Security\Domain\Activation\Ports\IStoreActivatedUsers;
use App\Security\Domain\Shared\Exception\InvalidToken;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\ValueObject\Token;
use App\Security\Domain\Shared\ValueObject\TokenType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Uuid;

final class MySqlActivatedUserStorage implements IStoreActivatedUsers
{
    private Connection $connection;
    private MySqlTokenStorage $tokenStorage;

    public function __construct(Connection $connection, MySqlTokenStorage $tokenStorage)
    {
        $this->connection = $connection;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @throws InvalidToken
     */
    public function findByActivationToken(string $tokenValue): UserToActivate
    {
        $result = $this->connection->createQueryBuilder()
            ->select('security_users.id, security_tokens.expire_at')
            ->from('security_users')
            ->join('security_users', 'security_tokens', 'security_tokens', 'security_users.id = security_tokens.user_id')
            ->where('security_tokens.value = :value')
            ->andWhere('security_tokens.type = :type')
            ->setParameter('value', $tokenValue)
            ->setParameter('type', TokenType::ACTIVATION->name)
            ->fetchAssociative();

        if ($result === false) {
            throw new InvalidToken();
        }

        return new UserToActivate(
            $result['id'],
            new \DateTimeImmutable($result['expire_at']),
        );
    }

    public function activate(Token $authenticationToken): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->update(
                'security_users',
                [
                    'is_active' => true,
                ],
                [
                    'id' => $authenticationToken->getUserId(),
                ],
                [
                    'updated_at' => 'datetime',
                    'is_active' => 'boolean',
                ]
            );

            $this->tokenStorage->removeToken($authenticationToken->getUserId(), TokenType::ACTIVATION);
            $this->tokenStorage->saveToken($authenticationToken);

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    public function get(Uuid $uuid): ActivatedUser
    {
        $result = $this->connection->createQueryBuilder()
            ->select('security_users.email, security_tokens.value')
            ->from('security_users')
            ->join('security_users', 'security_tokens', 'security_tokens', 'security_users.id = security_tokens.user_id')
            ->where('id = :id')
            ->andWhere('security_tokens.type = :type')
            ->setParameter('id', $uuid->jsonSerialize())
            ->setParameter('type', TokenType::AUTHENTICATION->name)
            ->fetchAssociative();

        if ($result === false) {
            throw new UserNotFound();
        }

        return new ActivatedUser(
            $result['email'],
            $result['value'],
        );
    }
}
