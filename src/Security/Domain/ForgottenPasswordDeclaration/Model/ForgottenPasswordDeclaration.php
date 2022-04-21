<?php
declare(strict_types=1);

namespace App\Security\Domain\ForgottenPasswordDeclaration\Model;

use JetBrains\PhpStorm\ArrayShape;

final class ForgottenPasswordDeclaration implements \JsonSerializable
{
    private string $forgottenPasswordTokenValue;

    public function __construct(string $forgottenPasswordTokenValue)
    {
        $this->forgottenPasswordTokenValue = $forgottenPasswordTokenValue;
    }

    #[ArrayShape(['forgottenPasswordTokenValue' => "string"])]
    public function jsonSerialize(): array
    {
        return [
            'forgottenPasswordTokenValue' => $this->forgottenPasswordTokenValue,
        ];
    }
}
