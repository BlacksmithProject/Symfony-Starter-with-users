<?php

declare(strict_types=1);

namespace App\Security\Domain\Model;

use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\Token;
use Symfony\Component\Uid\Uuid;

final class User
{
    private Uuid $uuid;
    private Email $email;
    private Password $password;
    private bool $isActive;
    private Token $token;

    public function __construct(
        Uuid $uuid,
        Email $email,
        Password $password,
        bool $isActive,
        Token $token
    ) {
        $this->uuid = $uuid;
        $this->email = $email;
        $this->password = $password;
        $this->isActive = $isActive;
        $this->token = $token;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getToken(): Token
    {
        return $this->token;
    }
}
