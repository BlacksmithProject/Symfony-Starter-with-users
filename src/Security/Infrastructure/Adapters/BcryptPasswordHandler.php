<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Authentication\Ports\IVerifyPasswords;
use App\Security\Domain\Registration\Ports\IHashPasswords;
use App\Security\Domain\Shared\Exception\InvalidPassword;
use App\Security\Domain\Shared\ValueObject\Password;

final class BcryptPasswordHandler implements IHashPasswords, IVerifyPasswords
{
    public function hash(Password $password): string
    {
        return password_hash($password->value(), PASSWORD_BCRYPT);
    }

    public function verify(Password $password, string $hash): void
    {
        if (password_verify($password->value(), $hash) === false) {
            throw new InvalidPassword();
        }
    }
}
