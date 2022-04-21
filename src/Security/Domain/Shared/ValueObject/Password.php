<?php
declare(strict_types=1);

namespace App\Security\Domain\Shared\ValueObject;

use App\Security\Domain\Shared\Exception\PasswordIsTooShort;

final class Password
{
    public const MIN_LENGTH = 6;
    private string $value;

    /** @throws PasswordIsTooShort */
    public function __construct(string $password)
    {
        if (mb_strlen($password) < self::MIN_LENGTH) {
            throw new PasswordIsTooShort();
        }
        $this->value = $password;
    }

    public function value(): string
    {
        return $this->value;
    }
}
