<?php

declare(strict_types=1);

namespace App\Security\Domain\Model;

use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Identity;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\Token;

final readonly class User
{
    public function __construct(
        private Identity $id,
        private Email $email,
        private Password $password,
        private bool $isActive,
        private Token $token
    ) {
    }

    public function getId(): Identity
    {
        return $this->id;
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
