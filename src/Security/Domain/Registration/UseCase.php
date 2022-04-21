<?php
declare(strict_types=1);

namespace App\Security\Domain\Registration;

use App\Security\Domain\Registration\Model\RegisteredUser;
use App\Security\Domain\Registration\Model\UserToRegister;
use App\Security\Domain\Registration\Ports\IHashPasswords;
use App\Security\Domain\Registration\Ports\IStoreRegisteredUsers;
use App\Security\Domain\Shared\Exception\EmailIsInvalidOrAlreadyTaken;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\Ports\IGenerateToken;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Password;
use App\Security\Domain\Shared\ValueObject\TokenType;

final class UseCase
{
    private IStoreRegisteredUsers $userStorage;
    private IHashPasswords $passwordHasher;
    private IGenerateToken $tokenGenerator;

    public function __construct(
        IStoreRegisteredUsers $userStorage,
        IHashPasswords $passwordHasher,
        IGenerateToken $tokenGenerator
    ) {
        $this->userStorage = $userStorage;
        $this->passwordHasher = $passwordHasher;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @throws EmailIsInvalidOrAlreadyTaken
     * @throws UserNotFound
     */
    public function execute(Email $email, Password $password): RegisteredUser
    {
        if ($this->userStorage->isEmailAlreadyUsed($email)) {
            throw new EmailIsInvalidOrAlreadyTaken();
        }

        $user = UserToRegister::create(
            $email,
            $this->passwordHasher->hash($password),
            $this->tokenGenerator->generate(TokenType::ACTIVATION)
        );

        $this->userStorage->create($user);

        return $this->userStorage->get($user->getUuid());
    }
}
