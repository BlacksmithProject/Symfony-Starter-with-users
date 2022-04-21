<?php
declare(strict_types=1);

namespace App\Security\Domain\Shared\ValueObject;

enum TokenType
{
    case ACTIVATION;
    case AUTHENTICATION;
}
