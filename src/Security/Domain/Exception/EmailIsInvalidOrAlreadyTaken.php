<?php

declare(strict_types=1);

namespace App\Security\Domain\Exception;

final class EmailIsInvalidOrAlreadyTaken extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Email is invalid or already taken');
    }
}
