<?php

declare(strict_types=1);

namespace App\Security\Domain\ValueObject;

use App\Security\Domain\Exception\PasswordIsTooShort;
use App\Security\Domain\Ports\IHashPasswords;

final class Password
{
    public const MIN_LENGTH = 6;
    private string $value;

    public function __construct(string $password)
    {
        $this->value = $password;
    }

    /**
     * @throws PasswordIsTooShort
     */
    public static function fromPlainPassword(string $plainPassword, IHashPasswords $passwordHasher): self
    {
        $plainPassword = trim($plainPassword, ' ');
        if (mb_strlen($plainPassword) < self::MIN_LENGTH) {
            throw new PasswordIsTooShort();
        }

        return new self($passwordHasher->hash($plainPassword));
    }

    public function value(): string
    {
        return $this->value;
    }
}
