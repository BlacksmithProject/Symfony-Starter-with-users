<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\ForgottenPasswordDeclaration\Model\ForgottenPasswordDeclaration;
use App\Security\Domain\ForgottenPasswordDeclaration\Ports\IStoreResetPasswordTokens;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Token;
use App\Security\Domain\Shared\ValueObject\TokenType;
use Doctrine\DBAL\Connection;

final class MySqlResetPasswordTokenStorage implements IStoreResetPasswordTokens
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function renewForUser(Email $email, Token $token): ForgottenPasswordDeclaration
    {
        $id = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('security_users')
            ->where('email = :email')
            ->setParameter('email', $email->value())
            ->fetchOne();

        if ($id === false) {
            throw new UserNotFound();
        }

        $this->connection->beginTransaction();
        try {
            $this->connection->delete(
                'security_tokens',
                [
                    'user_id' => $id,
                    'type' => TokenType::FORGOTTEN_PASSWORD->name,
                ]
            );
            $this->connection->insert(
                'security_tokens',
                [
                    'value' => $token->getValue(),
                    'created_at' => $token->getCreatedAt(),
                    'expire_at' => $token->getExpirationDate(),
                    'type' => $token->getTokenType()->name,
                    'user_id' => $id,
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

        return new ForgottenPasswordDeclaration($email->value(), $token->getValue());
    }
}
