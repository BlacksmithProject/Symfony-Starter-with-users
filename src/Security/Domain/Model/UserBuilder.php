<?php

declare(strict_types=1);

namespace App\Security\Domain\Model;

use App\Security\Domain\Ports\IGenerateTokens;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Identity;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\TokenType;

final readonly class UserBuilder
{
    public function __construct(private IGenerateTokens $tokenGenerator)
    {
    }

    public function buildInactiveWithActivationToken(Identity $id, Email $email, Password $password, \DateTimeImmutable $occurredOn): User
    {
        $activationToken = $this->tokenGenerator->generate($id, TokenType::ACTIVATION, $occurredOn);

        return new User($id, $email, $password, false, $activationToken);
    }

    public function buildActiveWithAuthenticationToken(Identity $id, Email $email, Password $password, \DateTimeImmutable $occurredOn): User
    {
        $authenticationToken = $this->tokenGenerator->generate($id, TokenType::AUTHENTICATION, $occurredOn);

        return new User($id, $email, $password, true, $authenticationToken);
    }

    public function buildWithForgottenPasswordToken(Identity $id, Email $email, Password $password, \DateTimeImmutable $occurredOn): User
    {
        $authenticationToken = $this->tokenGenerator->generate($id, TokenType::FORGOTTEN_PASSWORD, $occurredOn);

        return new User($id, $email, $password, true, $authenticationToken);
    }
}
