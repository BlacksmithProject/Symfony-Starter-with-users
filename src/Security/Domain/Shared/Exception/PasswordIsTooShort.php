<?php
declare(strict_types=1);

namespace App\Security\Domain\Shared\Exception;

use App\Security\Domain\Shared\ValueObject\Password;

final class PasswordIsTooShort extends \DomainException
{
    public function __construct()
    {
        parent::__construct('password should be at least '.Password::MIN_LENGTH.' characters long.');
    }
}
