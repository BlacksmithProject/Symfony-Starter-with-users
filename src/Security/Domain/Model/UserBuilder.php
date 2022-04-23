<?php
declare(strict_types=1);

namespace App\Security\Domain\Model;

use App\Security\Domain\Ports\IGenerateTokens;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\TokenType;
use Symfony\Component\Uid\Uuid;

final class UserBuilder
{
    private IGenerateTokens $tokenGenerator;

    public function __construct(IGenerateTokens $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    public function buildInactiveWithActivationToken(Uuid $id, Email $email, Password $password): User
    {
        $activationToken = $this->tokenGenerator->generate($id, TokenType::ACTIVATION);

        return new User($id, $email, $password, false, $activationToken);
    }

    public function buildActiveWithAuthenticationToken(Uuid $id, Email $email, Password $password): User
    {
        $authenticationToken = $this->tokenGenerator->generate($id, TokenType::AUTHENTICATION);

        return new User($id, $email, $password, true, $authenticationToken);
    }

    public function buildWithForgottenPasswordToken(Uuid $id, Email $email, Password $password): User
    {
        $authenticationToken = $this->tokenGenerator->generate($id, TokenType::FORGOTTEN_PASSWORD);

        return new User($id, $email, $password, true, $authenticationToken);
    }
}
