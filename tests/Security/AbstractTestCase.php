<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\Domain\Model\User;
use App\Security\Domain\Model\UserBuilder;
use App\Security\Domain\Ports\IGenerateTokens;
use App\Security\Domain\Ports\IHashPasswords;
use App\Security\Domain\Ports\IProvideIdentity;
use App\Security\Domain\Ports\IProvideTokens;
use App\Security\Domain\Ports\IProvideUsers;
use App\Security\Domain\Ports\IStoreTokens;
use App\Security\Domain\Ports\IStoreUsers;
use App\Security\Domain\Ports\IVerifyPasswords;
use App\Security\Domain\UseCase\Activation;
use App\Security\Domain\UseCase\Authentication;
use App\Security\Domain\UseCase\ForgottenPasswordDeclaration;
use App\Security\Domain\UseCase\PasswordReset;
use App\Security\Domain\UseCase\Registration;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\Token;
use App\Security\Infrastructure\Adapters\BcryptPasswordHandler;
use App\Security\Infrastructure\Adapters\TokensGenerator;
use App\Security\Infrastructure\Adapters\UuidProvider;
use App\Tests\Security\Adapters\FakeTokenStorage;
use App\Tests\Security\Adapters\FakeUserStorage;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected IGenerateTokens $tokenGenerator;
    protected IHashPasswords $passwordHasher;
    protected IProvideTokens $tokenProvider;
    protected IProvideUsers $userProvider;
    protected IStoreTokens $tokenStorage;
    protected IStoreUsers $userStorage;
    protected IVerifyPasswords $passwordVerifier;

    private UserBuilder $userBuilder;

    private IProvideIdentity $identityProvider;

    protected function setUp(): void
    {
        $tokenStorage = new FakeTokenStorage();
        $userStorage = new FakeUserStorage($tokenStorage);
        $passwordHandler = new BcryptPasswordHandler();
        $tokenGenerator = new TokensGenerator();
        $identityProvider = new UuidProvider();

        $this->tokenGenerator = $tokenGenerator;
        $this->passwordHasher = $passwordHandler;
        $this->tokenProvider = $tokenStorage;
        $this->userProvider = $userStorage;
        $this->tokenStorage = $tokenStorage;
        $this->userStorage = $userStorage;
        $this->passwordVerifier = $passwordHandler;
        $this->identityProvider = $identityProvider;

        $this->userBuilder = new UserBuilder($tokenGenerator);
    }

    protected function registerUser(
        \DateTimeImmutable $occurredOn,
        string $email = 'eddard.stark@winterfell.north',
        string $password = 'winterIsComing',
    ): Token {
        $email = new Email($email);
        $password = Password::fromPlainPassword($password, new BcryptPasswordHandler());

        return $this->getRegistration()->execute($email, $password, $occurredOn);
    }

    protected function registerAndActivateUser(
        \DateTimeImmutable $occurredOn,
        string $email = 'eddard.stark@winterfell.north',
        string $password = 'winterIsComing',
    ): User {
        $token = $this->registerUser($occurredOn, $email, $password);

        return $this->getActivation()->execute($token->getValue(), $occurredOn);
    }

    protected function registerAndDeclareForgottenPasswordUser(
        \DateTimeImmutable $occurredOn,
        string $email = 'eddard.stark@winterfell.north',
        string $password = 'winterIsComing',
    ): User {
        $token = $this->registerUser($occurredOn, $email, $password);
        $this->getActivation()->execute($token->getValue(), $occurredOn);

        return $this->getForgottenPasswordDeclaration()->execute(new Email($email), $occurredOn);
    }

    protected function getRegistration(): Registration
    {
        return new Registration(
            $this->userStorage,
            $this->userBuilder,
            $this->tokenProvider,
            $this->identityProvider,
        );
    }

    protected function getActivation(): Activation
    {
        return new Activation(
            $this->tokenProvider,
            $this->userProvider,
            $this->userStorage,
            $this->userBuilder,
        );
    }

    protected function getAuthentication(): Authentication
    {
        return new Authentication(
            $this->userProvider,
            $this->tokenStorage,
            $this->tokenGenerator,
            $this->passwordVerifier
        );
    }

    protected function getForgottenPasswordDeclaration(): ForgottenPasswordDeclaration
    {
        return new ForgottenPasswordDeclaration(
            $this->userProvider,
            $this->userBuilder,
            $this->userStorage
        );
    }

    protected function getPasswordReset(): PasswordReset
    {
        return new PasswordReset(
            $this->tokenProvider,
            $this->userProvider,
            $this->userBuilder,
            $this->userStorage
        );
    }
}
