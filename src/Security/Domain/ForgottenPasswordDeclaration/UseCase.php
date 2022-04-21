<?php
declare(strict_types=1);

namespace App\Security\Domain\ForgottenPasswordDeclaration;

use App\Security\Domain\ForgottenPasswordDeclaration\Model\ForgottenPasswordDeclaration;
use App\Security\Domain\ForgottenPasswordDeclaration\Ports\IStoreResetPasswordTokens;
use App\Security\Domain\Shared\Exception\UserNotFound;
use App\Security\Domain\Shared\Ports\IGenerateToken;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\TokenType;

final class UseCase
{
    private IStoreResetPasswordTokens $tokenStorage;
    private IGenerateToken $tokenGenerator;

    public function __construct(IStoreResetPasswordTokens $tokenStorage, IGenerateToken $tokenGenerator)
    {
        $this->tokenStorage = $tokenStorage;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @throws UserNotFound
     */
    public function execute(Email $email): ForgottenPasswordDeclaration
    {
        return $this->tokenStorage->renewForUser($email, $this->tokenGenerator->generate(TokenType::FORGOTTEN_PASSWORD));
    }
}
