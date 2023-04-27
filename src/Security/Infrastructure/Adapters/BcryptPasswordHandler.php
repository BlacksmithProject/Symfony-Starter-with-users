<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Exception\InvalidPassword;
use App\Security\Domain\Ports\IHashPasswords;
use App\Security\Domain\Ports\IVerifyPasswords;

final class BcryptPasswordHandler implements IHashPasswords, IVerifyPasswords
{
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify(string $password, string $hash): void
    {
        if (password_verify($password, $hash) === false) {
            throw new InvalidPassword();
        }
    }
}
