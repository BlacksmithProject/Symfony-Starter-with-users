<?php

namespace App\Security\Domain\Ports;

use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\User;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Identity;

interface IProvideUsers
{
    /** @throws UserNotFound */
    public function getUserToActivate(Identity $userId): User;

    /** @throws UserNotFound */
    public function getActivatedUser(Email $email): User;

    /** @throws UserNotFound */
    public function getForgottenPasswordUser(Identity $userId): User;
}
