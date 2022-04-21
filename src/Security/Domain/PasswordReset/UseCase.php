<?php
declare(strict_types=1);

namespace App\Security\Domain\PasswordReset;

use App\Security\Domain\PasswordReset\Ports\IStorePasswordResetUsers;
use App\Security\Domain\Shared\Exception\InvalidToken;
use App\Security\Domain\Shared\Exception\TokenIsExpired;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\Model\AuthenticatedUser;
use App\Security\Domain\Shared\Ports\IGenerateToken;
use App\Security\Domain\Shared\Ports\IHashPasswords;
use App\Security\Domain\Shared\Ports\IStoreAuthenticatedUsers;
use App\Security\Domain\Shared\ValueObject\Password;
use App\Security\Domain\Shared\ValueObject\TokenType;

final class UseCase
{
    private IStorePasswordResetUsers $passwordResetUserStorage;
    private IHashPasswords $passwordHasher;
    private IStoreAuthenticatedUsers $authenticatedUserStorage;
    private IGenerateToken $tokenGenerator;

    public function __construct(
        IStorePasswordResetUsers $passwordResetUserStorage,
        IHashPasswords $passwordHasher,
        IStoreAuthenticatedUsers $authenticatedUserStorage,
        IGenerateToken $tokenGenerator
    ) {
        $this->passwordResetUserStorage = $passwordResetUserStorage;
        $this->passwordHasher = $passwordHasher;
        $this->authenticatedUserStorage = $authenticatedUserStorage;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @throws InvalidToken
     * @throws TokenIsExpired
     * @throws UserNotFound
     */
    public function execute(string $forgottenPasswordTokenValue, Password $newPlainPassword): AuthenticatedUser
    {
        $user = $this->passwordResetUserStorage->get($forgottenPasswordTokenValue);

        if ($user->forgottenPasswordTokenHasExpired()) {
            $this->passwordResetUserStorage->remove($user->getId());
            throw new TokenIsExpired();
        }

        $user->updatePassword($this->passwordHasher->hash($newPlainPassword));

        $this->passwordResetUserStorage->resetPassword($user, $this->tokenGenerator->generate($user->getId(), TokenType::AUTHENTICATION));

        return $this->authenticatedUserStorage->getByEmail($user->getEmail());
    }
}
