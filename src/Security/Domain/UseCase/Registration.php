<?php

declare(strict_types=1);

namespace App\Security\Domain\UseCase;

use App\Security\Domain\Exception\EmailIsInvalidOrAlreadyTaken;
use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\Model\UserBuilder;
use App\Security\Domain\Ports\IProvideTokens;
use App\Security\Domain\Ports\IStoreUsers;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\Token;
use App\Security\Domain\ValueObject\TokenType;
use Symfony\Component\Uid\Uuid;

final class Registration
{
    private IStoreUsers $userStorage;
    private UserBuilder $userBuilder;
    private IProvideTokens $tokenProvider;

    public function __construct(
        IStoreUsers $userStorage,
        UserBuilder $userBuilder,
        IProvideTokens $tokenProvider,
    ) {
        $this->userStorage = $userStorage;
        $this->userBuilder = $userBuilder;
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * @throws EmailIsInvalidOrAlreadyTaken
     * @throws TokenNotFound                - Should NEVER happen
     */
    public function execute(Email $email, Password $password, \DateTimeImmutable $occurredOn): Token
    {
        if ($this->userStorage->isEmailAlreadyUsed($email)) {
            throw new EmailIsInvalidOrAlreadyTaken();
        }

        $userId = Uuid::v4();

        $user = $this->userBuilder->buildInactiveWithActivationToken($userId, $email, $password, $occurredOn);

        $this->userStorage->add($user);

        return $this->tokenProvider->getToken($userId, TokenType::ACTIVATION);
    }
}
