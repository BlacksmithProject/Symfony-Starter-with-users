<?php
declare(strict_types=1);

namespace App\Security\Domain\Registration\Model;

use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Token;
use Symfony\Component\Uid\Uuid;

final class UserToRegister
{
    private Uuid $uuid;
    private Email $email;
    private string $password;
    private bool $isActive;
    private Token $activationToken;

    public function __construct( //TODO: repasser en privé
        Uuid $uuid,
        Email $email,
        string $password,
        Token $activationToken
    ) {
        $this->uuid = $uuid;
        $this->email = $email;
        $this->password = $password;
        $this->isActive = false;
        $this->activationToken = $activationToken;
    }

    public static function create(Email $email, string $password, Token $activationToken): self
    {
        return new self(Uuid::v4(), $email, $password, $activationToken);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getActivationToken(): Token
    {
        return $this->activationToken;
    }
}
