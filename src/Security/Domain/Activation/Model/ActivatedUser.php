<?php
declare(strict_types=1);

namespace App\Security\Domain\Activation\Model;

use JetBrains\PhpStorm\ArrayShape;

final class ActivatedUser implements \JsonSerializable
{
    private string $email;
    private string $authenticationTokenValue;

    public function __construct(string $email, string $authenticationTokenValue)
    {
        $this->email = $email;
        $this->authenticationTokenValue = $authenticationTokenValue;
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
