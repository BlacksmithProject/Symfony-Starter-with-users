<?php

namespace App\Security\Domain\Ports;

use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\AuthenticatedUser;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Token;

interface IStoreAuthenticatedUsers
{
    /** @throws UserNotFound */
    public function getByEmail(Email $email): AuthenticatedUser;

    public function renewAuthenticationToken(Token $authenticationToken): void;
}
