<?php

namespace App\Security\Domain\Ports;

use App\Security\Domain\Model\User;
use App\Security\Domain\ValueObject\Email;

interface IStoreUsers
{
    public function isEmailAlreadyUsed(Email $email): bool;

    public function add(User $user): void;

    public function activate(User $user): void;

    public function renewForgottenPassword(User $user): void;

    public function updatePassword(User $user): void;
}
