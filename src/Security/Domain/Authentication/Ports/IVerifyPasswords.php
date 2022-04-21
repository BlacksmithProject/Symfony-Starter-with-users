<?php
declare(strict_types=1);

namespace App\Security\Domain\Authentication\Ports;

use App\Security\Domain\Shared\Exception\InvalidPassword;
use App\Security\Domain\Shared\ValueObject\Password;

interface IVerifyPasswords
{
    /** @throws InvalidPassword */
    public function verify(Password $password, string $hash): void;
}
