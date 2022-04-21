<?php

namespace App\Security\Domain\Authentication\Ports;

use App\Security\Domain\Authentication\Model\AuthenticatedUser;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Token;

interface IStoreAuthenticatedUsers
{
    /** @throws UserNotFound */
    public function getByEmail(Email $email): AuthenticatedUser;

    public function renewAuthenticationToken(string $userId, Token $authenticationToken): void;
}
