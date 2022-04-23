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
use App\Security\Domain\ValueObject\Token;
use App\Security\Domain\ValueObject\TokenType;

final class Activation
{
    private IProvideTokens $tokenProvider;
    private IProvideUsers $userProvider;
    private IStoreUsers $userStorage;
    private UserBuilder $userBuilder;

    public function __construct(
        IProvideTokens $tokenProvider,
        IProvideUsers $userProvider,
        IStoreUsers $userStorage,
        UserBuilder $userBuilder
    ) {
        $this->tokenProvider = $tokenProvider;
        $this->userProvider = $userProvider;
        $this->userStorage = $userStorage;
        $this->userBuilder = $userBuilder;
    }

    /**
     * @throws TokenIsExpired
     * @throws TokenNotFound
     * @throws UserNotFound
     */
    public function execute(string $tokenValue): User
    {
        $activationToken = $this->tokenProvider->getTokenByValue($tokenValue, TokenType::ACTIVATION);

        if ($activationToken->isExpired()) {
            throw new TokenIsExpired();
        }

        $userToActivate = $this->userProvider->getUserToActivate($activationToken->getUserId());
        $activatedUser = $this->userBuilder->buildActiveWithAuthenticationToken(
            $userToActivate->getUuid(),
            $userToActivate->getEmail(),
            $userToActivate->getPassword()
        );

        $this->userStorage->activate($activatedUser);

        return $activatedUser;
    }
}
