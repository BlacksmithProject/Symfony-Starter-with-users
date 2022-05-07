<?php
declare(strict_types=1);

namespace App\Security\Domain\UseCase;

use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\User;
use App\Security\Domain\Model\UserBuilder;
use App\Security\Domain\Ports\IProvideUsers;
use App\Security\Domain\Ports\IStoreUsers;
use App\Security\Domain\ValueObject\Email;

final class ForgottenPasswordDeclaration
{
    private IProvideUsers $userProvider;
    private UserBuilder $userBuilder;
    private IStoreUsers $userStorage;

    public function __construct(IProvideUsers $userProvider, UserBuilder $userBuilder, IStoreUsers $userStorage)
    {
        $this->userProvider = $userProvider;
        $this->userBuilder = $userBuilder;
        $this->userStorage = $userStorage;
    }

    /**
     * @throws UserNotFound
     */
    public function execute(Email $email, \DateTimeImmutable $occurredOn): User
    {
        $activatedUser = $this->userProvider->getActivatedUser($email);
        $forgottenPasswordUser = $this->userBuilder->buildWithForgottenPasswordToken(
            $activatedUser->getUuid(),
            $activatedUser->getEmail(),
            $activatedUser->getPassword(),
            $occurredOn
        );

        $this->userStorage->renewForgottenPassword($forgottenPasswordUser);

        return $forgottenPasswordUser;
    }
}
