<?php
declare(strict_types=1);

namespace App\Security\Domain\Activation;

use App\Security\Domain\Activation\Model\ActivatedUser;
use App\Security\Domain\Activation\Ports\IStoreActivatedUsers;
use App\Security\Domain\Shared\Exception\InvalidToken;
use App\Security\Domain\Shared\Exception\TokenIsExpired;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\Ports\IGenerateToken;
use App\Security\Domain\Shared\ValueObject\TokenType;
use Symfony\Component\Uid\Uuid;

final class UseCase
{
    private IStoreActivatedUsers $userStorage;
    private IGenerateToken $tokenGenerator;

    public function __construct(IStoreActivatedUsers $userStorage, IGenerateToken $tokenGenerator)
    {
        $this->userStorage = $userStorage;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @throws UserNotFound
     * @throws InvalidToken
     * @throws TokenIsExpired
     */
    public function execute(string $tokenValue): ActivatedUser
    {
        $userToActivate = $this->userStorage->findByActivationToken($tokenValue);

        if ($userToActivate->activationTokenHasExpired()) {
            throw new TokenIsExpired();
        }

        $authenticationToken = $this->tokenGenerator->generate(TokenType::AUTHENTICATION);

        $this->userStorage->activate($userToActivate, $authenticationToken);

        return $this->userStorage->get(Uuid::fromString($userToActivate->getId()));
    }
}
