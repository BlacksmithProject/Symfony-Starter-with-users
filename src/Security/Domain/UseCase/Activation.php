<?php

declare(strict_types=1);

namespace App\Security\Domain\UseCase;

use App\Security\Domain\Exception\TokenIsExpired;
use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\User;
use App\Security\Domain\Model\UserBuilder;
use App\Security\Domain\Ports\IProvideTokens;
use App\Security\Domain\Ports\IProvideUsers;
use App\Security\Domain\Ports\IStoreUsers;
use App\Security\Domain\ValueObject\TokenType;

final readonly class Activation
{
    public function __construct(
        private IProvideTokens $tokenProvider,
        private IProvideUsers $userProvider,
        private IStoreUsers $userStorage,
        private UserBuilder $userBuilder
    ) {
    }

    /**
     * @throws TokenIsExpired
     * @throws TokenNotFound
     * @throws UserNotFound   - should NEVER happen
     */
    public function execute(string $tokenValue, \DateTimeImmutable $occurredOn): User
    {
        $activationToken = $this->tokenProvider->getTokenByValue($tokenValue, TokenType::ACTIVATION);

        if ($activationToken->isExpired()) {
            throw new TokenIsExpired();
        }

        $userToActivate = $this->userProvider->getUserToActivate($activationToken->getUserId());
        $activatedUser = $this->userBuilder->buildActiveWithAuthenticationToken(
            $userToActivate->getId(),
            $userToActivate->getEmail(),
            $userToActivate->getPassword(),
            $occurredOn
        );

        $this->userStorage->activate($activatedUser);

        return $activatedUser;
    }
}
