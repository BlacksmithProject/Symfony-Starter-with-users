<?php

declare(strict_types=1);

namespace App\Security\Domain\UseCase;

use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\User;
use App\Security\Domain\Model\UserBuilder;
use App\Security\Domain\Ports\IProvideUsers;
use App\Security\Domain\Ports\IStoreUsers;
use App\Security\Domain\ValueObject\Email;

final readonly class ForgottenPasswordDeclaration
{
    public function __construct(
        private IProvideUsers $userProvider,
        private UserBuilder $userBuilder,
        private IStoreUsers $userStorage,
    ) {
    }

    /**
     * @throws UserNotFound
     */
    public function execute(Email $email, \DateTimeImmutable $occurredOn): User
    {
        $activatedUser = $this->userProvider->getActivatedUser($email);
        $forgottenPasswordUser = $this->userBuilder->buildWithForgottenPasswordToken(
            $activatedUser->getId(),
            $activatedUser->getEmail(),
            $activatedUser->getPassword(),
            $occurredOn,
        );

        $this->userStorage->renewForgottenPassword($forgottenPasswordUser);

        return $forgottenPasswordUser;
    }
}
