<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Http\Registration;

use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;

final class Input
{
    public Email $email;
    public Password $password;
}
