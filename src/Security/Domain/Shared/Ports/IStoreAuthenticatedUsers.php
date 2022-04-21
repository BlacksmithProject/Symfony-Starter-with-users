<?php

namespace App\Security\Domain\Shared\Ports;

use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\Model\AuthenticatedUser;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Token;

interface IStoreAuthenticatedUsers
{
    /** @throws UserNotFound */
    public function getByEmail(Email $email): AuthenticatedUser;

    public function renewAuthenticationToken(Token $authenticationToken): void;
}
