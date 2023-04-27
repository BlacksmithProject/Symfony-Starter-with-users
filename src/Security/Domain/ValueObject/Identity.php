<?php

declare(strict_types=1);

namespace App\Security\Domain\ValueObject;

final readonly class Identity
{
    public function __construct(public string $value)
    {
    }

    public function equals(Identity $identity): bool
    {
        return $identity->value === $this->value;
    }
}
