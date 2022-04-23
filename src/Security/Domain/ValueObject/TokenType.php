<?php
declare(strict_types=1);

namespace App\Security\Domain\ValueObject;

enum TokenType: string
{
    case ACTIVATION = 'ACTIVATION';
    case AUTHENTICATION = 'AUTHENTICATION';
    case FORGOTTEN_PASSWORD = 'FORGOTTEN_PASSWORD';
}
