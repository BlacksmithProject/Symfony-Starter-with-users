<?php

declare(strict_types=1);

namespace App\Security\Domain\Exception;

final class InvalidToken extends \DomainException
{
    public function __construct()
    {
        parent::__construct('invalid token');
    }
}
