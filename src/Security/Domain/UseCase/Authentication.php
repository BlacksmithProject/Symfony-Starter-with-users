<?php

declare(strict_types=1);

namespace App\Security\Domain\UseCase;

use App\Security\Domain\Exception\InvalidPassword;
use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\User;
use App\Security\Domain\Ports\IGenerateTokens;
use App\Security\Domain\Ports\IProvideUsers;
use App\Security\Domain\Ports\IStoreTokens;
use App\Security\Domain\Ports\IVerifyPasswords;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\TokenType;

final readonly class Authentication
{
    public function __construct(
        private IProvideUsers $userProvider,
        private IStoreTokens $tokenStorage,
        private IGenerateTokens $tokenGenerator,
        private IVerifyPasswords $passwordVerifier
    ) {
    }

    /**
     * @throws UserNotFound
     * @throws InvalidPassword
     */
    public function execute(Email $email, Password $password, \DateTimeImmutable $occurredOn): User
    {
        $user = $this->userProvider->getActivatedUser($email);
        $this->passwordVerifier->verify($password->value(), $user->getPassword()->value());

        if ($user->getToken()->isExpired()) {
            $this->tokenStorage->renew(
                $this->tokenGenerator->generate(
                    $user->getId(),
                    TokenType::AUTHENTICATION,
                    $occurredOn,
                ),
            );

            $user = $this->userProvider->getActivatedUser($email);
        }

        return $user;
    }
}
