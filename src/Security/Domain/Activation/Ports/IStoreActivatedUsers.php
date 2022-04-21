<?php
declare(strict_types=1);

namespace App\Security\Domain\Activation\Ports;

use App\Security\Domain\Activation\Model\ActivatedUser;
use App\Security\Domain\Activation\Model\UserToActivate;
use App\Security\Domain\Shared\Exception\InvalidToken;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\ValueObject\Token;
use Symfony\Component\Uid\Uuid;

interface IStoreActivatedUsers
{
    /** @throws InvalidToken */
    public function findByActivationToken(string $tokenValue): UserToActivate;

    public function activate(Token $authenticationToken): void;

    /** @throws UserNotFound */
    public function get(Uuid $uuid): ActivatedUser;
}
