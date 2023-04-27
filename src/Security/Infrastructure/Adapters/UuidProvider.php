<?php

declare(strict_types=1);

namespace App\Security\Infrastructure\Adapters;

use App\Security\Domain\Ports\IProvideIdentity;
use App\Security\Domain\ValueObject\Identity;
use Symfony\Component\Uid\Uuid;

final class UuidProvider implements IProvideIdentity
{
    public function generate(): Identity
    {
        return new Identity(Uuid::v4()->toRfc4122());
    }
}
