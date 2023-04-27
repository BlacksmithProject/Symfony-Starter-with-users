<?php

namespace App\Security\Domain\Ports;

use App\Security\Domain\ValueObject\Identity;

interface IProvideIdentity
{
    public function generate(): Identity;
}
