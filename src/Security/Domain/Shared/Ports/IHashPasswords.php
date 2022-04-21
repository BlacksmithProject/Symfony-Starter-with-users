<?php

namespace App\Security\Domain\Shared\Ports;

use App\Security\Domain\Shared\ValueObject\Password;

interface IHashPasswords
{
    public function hash(Password $password): string;
}
