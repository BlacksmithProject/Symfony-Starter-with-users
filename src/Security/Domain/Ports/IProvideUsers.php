<?php

namespace App\Security\Domain\Ports;

use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\User;
use App\Security\Domain\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

interface IProvideUsers
{
    /** @throws UserNotFound */
    public function getUserToActivate(Uuid $userId): User;

    /** @throws UserNotFound */
    public function getActivatedUser(Email $email): User;

    /** @throws UserNotFound */
    public function getForgottenPasswordUser(Uuid $userId): User;
}
