<?php

namespace App\Security\Domain\ForgottenPasswordDeclaration\Ports;

use App\Security\Domain\ForgottenPasswordDeclaration\Model\ForgottenPasswordDeclaration;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Token;

interface IStoreResetPasswordTokens
{
    /** @throws UserNotFound */
    public function renewForUser(Email $email, Token $token): ForgottenPasswordDeclaration;
}
