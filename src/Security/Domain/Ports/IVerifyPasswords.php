<?php

declare(strict_types=1);

namespace App\Security\Domain\Ports;

use App\Security\Domain\Exception\InvalidPassword;

interface IVerifyPasswords
{
    /** @throws InvalidPassword */
    public function verify(string $password, string $hash): void;
}
