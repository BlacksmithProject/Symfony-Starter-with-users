<?php
declare(strict_types=1);

namespace App\Security\Domain\Exception;

final class UserNotFound extends \DomainException
{
    public function __construct()
    {
        parent::__construct('User not found');
    }
}
