<?php
declare(strict_types=1);

namespace App\Security\Domain\ForgottenPasswordDeclaration\Model;

use JetBrains\PhpStorm\ArrayShape;

final class ForgottenPasswordDeclaration implements \JsonSerializable
{
    private string $email;
    private string $forgottenPasswordTokenValue;

    public function __construct(string $email, string $forgottenPasswordTokenValue)
    {
        $this->email = $email;
        $this->forgottenPasswordTokenValue = $forgottenPasswordTokenValue;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getForgottenPasswordTokenValue(): string
    {
        return $this->forgottenPasswordTokenValue;
    }

    #[ArrayShape(['email' => "string", 'forgottenPasswordTokenValue' => "string"])]
    public function jsonSerialize(): array
    {
        return [
            'email' => $this->email,
            'forgottenPasswordTokenValue' => $this->forgottenPasswordTokenValue,
        ];
    }
}
