<?php
declare(strict_types=1);

namespace App\Security\Domain\Activation\Model;

final class UserToActivate
{
    private string $id;
    private \DateTimeImmutable $activationTokenExpirationDate;

    public function __construct(string $id, \DateTimeImmutable $activationTokenExpirationDate)
    {
        $this->id = $id;
        $this->activationTokenExpirationDate = $activationTokenExpirationDate;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function activationTokenHasExpired(): bool
    {
        return $this->activationTokenExpirationDate < new \DateTimeImmutable();
    }
}
