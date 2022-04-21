<?php
declare(strict_types=1);

namespace App\Security\Domain\Registration\Model;

use JetBrains\PhpStorm\ArrayShape;

final class RegisteredUser implements \JsonSerializable
{
    private string $email;
    private string $activationTokenValue;

    public function __construct(string $email, string $activationTokenValue)
    {
        $this->email = $email;
        $this->activationTokenValue = $activationTokenValue;
    }

    #[ArrayShape(['email' => "string", 'activationTokenValue' => "string"])]
    public function jsonSerialize(): array
    {
        return [
            'email' => $this->email,
            'activationTokenValue' => $this->activationTokenValue,
        ];
    }
}
