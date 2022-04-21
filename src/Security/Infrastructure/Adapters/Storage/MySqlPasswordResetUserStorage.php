<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters\Storage;

use App\Security\Domain\PasswordReset\Model\UserToResetPassword;
use App\Security\Domain\PasswordReset\Ports\IStorePasswordResetUsers;
use App\Security\Domain\Shared\Exception\InvalidToken;
use App\Security\Domain\Shared\ValueObject\Token;
use App\Security\Domain\Shared\ValueObject\TokenType;
use Doctrine\DBAL\Connection;

final class MySqlPasswordResetUserStorage implements IStorePasswordResetUsers
{
    private Connection $connection;
    private MySqlTokenStorage $mySqlTokenStorage;

    public function __construct(Connection $connection, MySqlTokenStorage $mySqlTokenStorage)
    {
        $this->connection = $connection;
        $this->mySqlTokenStorage = $mySqlTokenStorage;
    }

    /**
     * @throws InvalidToken
     */
    public function get(string $forgottenPasswordTokenValue): UserToResetPassword
    {
        $result = $this->connection->createQueryBuilder()
            ->select('security_users.id, security_users.password, security_users.email, security_tokens.expire_at')
            ->from('security_users')
            ->join('security_users', 'security_tokens', 'security_tokens', 'security_users.id = security_tokens.user_id')
            ->where('security_tokens.value = :value')
            ->andWhere('security_tokens.type = :type')
            ->setParameter('value', $forgottenPasswordTokenValue)
            ->setParameter('type', TokenType::FORGOTTEN_PASSWORD->name)
            ->fetchAssociative();

        if ($result === false) {
            throw new InvalidToken();
        }

        return new UserToResetPassword($result['id'], $result['password'], $result['email'], new \DateTimeImmutable($result['expire_at']));
    }

    public function resetPassword(UserToResetPassword $user, Token $authenticationToken): void
    {
        $this->connection->beginTransaction();

        try {
            $this->connection->update(
                'security_users',
                [
                    'password' => $user->getPassword(),
                ],
                [
                    'id' => $user->getId(),
                ]
            );

            $this->mySqlTokenStorage->removeToken($user->getId(), TokenType::FORGOTTEN_PASSWORD);
            $this->mySqlTokenStorage->removeToken($user->getId(), TokenType::AUTHENTICATION);
            $this->mySqlTokenStorage->saveToken($authenticationToken);

            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        }
    }

    public function remove(string $userId): void
    {
        $this->mySqlTokenStorage->removeToken($userId, TokenType::FORGOTTEN_PASSWORD);
    }
}
