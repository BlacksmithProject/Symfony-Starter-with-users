<?php
declare(strict_types=1);

namespace App\Security\Domain\Authentication;

use App\Security\Domain\Authentication\Model\AuthenticatedUser;
use App\Security\Domain\Authentication\Ports\IStoreAuthenticatedUsers;
use App\Security\Domain\Authentication\Ports\IVerifyPasswords;
use App\Security\Domain\Shared\Exception\InvalidPassword;
use App\Security\Domain\Shared\Exception\TokenIsExpired;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\Ports\IGenerateToken;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Password;
use App\Security\Domain\Shared\ValueObject\TokenType;

final class UseCase
{
    private IStoreAuthenticatedUsers $userStorage;
    private IVerifyPasswords $passwordVerifier;
    private IGenerateToken $tokenGenerator;

    public function __construct(IStoreAuthenticatedUsers $userStorage, IVerifyPasswords $passwordVerifier, IGenerateToken $tokenGenerator)
    {
        $this->userStorage = $userStorage;
        $this->passwordVerifier = $passwordVerifier;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @throws UserNotFound
     * @throws InvalidPassword
     * @throws TokenIsExpired
     */
    public function execute(Email $email, Password $password): AuthenticatedUser
    {
        $user = $this->userStorage->getByEmail($email);

        if ($user->authenticationTokenHasExpired()) {
            $this->userStorage->renewAuthenticationToken($user->getId(), $this->tokenGenerator->generate(TokenType::AUTHENTICATION));

            $user = $this->userStorage->getByEmail($email);
        }

        $this->passwordVerifier->verify($password, $user->getHashedPassword());

        return $user;
    }
}
