<?php
declare(strict_types=1);

namespace App\Tests\Security\UseCase;

use App\Security\Domain\UseCase\PasswordReset;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\TokenType;
use App\Tests\Security\AbstractTestCase;

final class PasswordResetTest extends AbstractTestCase
{
    private PasswordReset $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = $this->getPasswordReset();
    }

    /** @test */
    public function password should be reset(): void
    {
        // GIVEN
        $user = $this->registerAndDeclareForgottenPasswordUser(new \DateTimeImmutable());
        $password = Password::fromPlainPassword('aNewPassword', $this->passwordHasher);

        // WHEN
        $user = $this->useCase->execute($user->getToken()->getValue(), $password, new \DateTimeImmutable());
        $authenticatedUser = $this->getAuthentication()->execute($user->getEmail(), new Password('aNewPassword'), new \DateTimeImmutable());

        // THEN
        self::assertSame(TokenType::AUTHENTICATION, $authenticatedUser->getToken()->getTokenType());
    }
}
