<?php
declare(strict_types=1);

namespace App\Security\Domain\Registration\Ports;

use App\Security\Domain\Registration\Model\RegisteredUser;
use App\Security\Domain\Registration\Model\UserToRegister;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

interface IStoreRegisteredUsers
{
    public function isEmailAlreadyUsed(Email $email): bool;

    public function create(UserToRegister $user): void;

    /** @throws UserNotFound */
    public function get(Uuid $uuid): RegisteredUser;
}
