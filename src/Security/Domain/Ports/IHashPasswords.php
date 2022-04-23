<?php

namespace App\Security\Domain\Ports;

interface IHashPasswords
{
    public function hash(string $password): string;
}
