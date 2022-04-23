<?php
declare(strict_types=1);

namespace App\Security\Domain\ValueObject;

use App\Security\Domain\Exception\EmailIsInvalidOrAlreadyTaken;

final class Email
{
    private string $value;

    /** @throws EmailIsInvalidOrAlreadyTaken */
    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new EmailIsInvalidOrAlreadyTaken();
        }

        $this->value = $email;
    }

    public function equals(Email $email): bool
    {
        return $this->value === $email->value();
    }

    public function value(): string
    {
        return $this->value;
    }
}
