<?php

namespace App\Security\Domain\PasswordReset\Ports;

use App\Security\Domain\PasswordReset\Model\UserToResetPassword;
use App\Security\Domain\Shared\Exception\InvalidToken;
use App\Security\Domain\Shared\ValueObject\Token;

interface IStorePasswordResetUsers
{
    /**
     * @throws InvalidToken
     */
    public function get(string $forgottenPasswordTokenValue): UserToResetPassword;

    public function resetPassword(UserToResetPassword $user, Token $authenticationToken): void;

    public function remove(string $userId): void;
}
