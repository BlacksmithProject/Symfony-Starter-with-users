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
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\TokenType;

final readonly class PasswordReset
{
    public function __construct(
        private IProvideTokens $tokenProvider,
        private IProvideUsers $userProvider,
        private UserBuilder $userBuilder,
        private IStoreUsers $userStorage,
    ) {
    }

    /**
     * @throws TokenIsExpired
     * @throws UserNotFound
     * @throws TokenNotFound
     */
    public function execute(string $forgottenPasswordTokenValue, Password $newPassword, \DateTimeImmutable $occurredOn): User
    {
        $token = $this->tokenProvider->getTokenByValue($forgottenPasswordTokenValue, TokenType::FORGOTTEN_PASSWORD);

        if ($token->isExpired()) {
            throw new TokenIsExpired();
        }

        $forgottenPasswordUser = $this->userProvider->getForgottenPasswordUser($token->getUserId());

        $authenticatedUser = $this->userBuilder->buildActiveWithAuthenticationToken(
            $forgottenPasswordUser->getId(),
            $forgottenPasswordUser->getEmail(),
            $newPassword,
            $occurredOn,
        );

        $this->userStorage->updatePassword($authenticatedUser);

        return $authenticatedUser;
    }
}
