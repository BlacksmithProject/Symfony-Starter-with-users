<?php
declare(strict_types=1);

namespace App\Security\Domain\Authentication\Model;

use JetBrains\PhpStorm\ArrayShape;

final class AuthenticatedUser implements \JsonSerializable
{
    private string $email;
    private string $hashedPassword;
    private string $authenticationTokenValue;
    private \DateTimeImmutable $authenticationTokenExpirationDate;
    private string $id;

    public function __construct(string $id, string $email, string $hashedPassword, string $authenticationTokenValue, \DateTimeImmutable $authenticationTokenExpirationDate)
    {
        $this->id = $id;
        $this->email = $email;
        $this->hashedPassword = $hashedPassword;
        $this->authenticationTokenValue = $authenticationTokenValue;
        $this->authenticationTokenExpirationDate = $authenticationTokenExpirationDate;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function authenticationTokenHasExpired(): bool
    {
        return $this->authenticationTokenExpirationDate < new \DateTimeImmutable();
    }

    #[ArrayShape(['email' => "string", 'authenticationTokenValue' => "string"])]
    public function jsonSerialize(): array
    {
        return [
            'email' => $this->email,
            'authenticationTokenValue' => $this->authenticationTokenValue,
        ];
    }
}
