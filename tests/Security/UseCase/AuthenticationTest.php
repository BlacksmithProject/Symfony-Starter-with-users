<?php
declare(strict_types=1);

namespace App\Tests\Security\UseCase;

use App\Security\Domain\Exception\InvalidPassword;
use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\UseCase\Authentication;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\TokenType;
use App\Tests\Security\AbstractTestCase;

final class AuthenticationTest extends AbstractTestCase
{
    private Authentication $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = $this->getAuthentication();
    }

    /** @test */
    public function an active user can authenticate with credentials(): void
    {
        // GIVEN
        $activatedUser = $this->registerAndActivateUser(
            new \DateTimeImmutable(),
            password: 'secret!'
        );

        // WHEN
        $user = $this->useCase->execute(
            $activatedUser->getEmail(),
            new Password('secret!'),
            new \DateTimeImmutable()
        );

        // THEN
        self::assertSame(TokenType::AUTHENTICATION, $user->getToken()->getTokenType());
    }

    /** @test */
    public function cannot authenticate with inexistant account(): void
    {
        // EXPECT
        self::expectException(UserNotFound::class);

        // WHEN
        $this->useCase->execute(
            new Email('email@example.com'),
            new Password('secret!'),
            new \DateTimeImmutable()
        );
    }

    /** @test */
    public function cannot authenticate with invalid password(): void
    {
        // EXPECT
        self::expectException(InvalidPassword::class);

        // GIVEN
        $user = $this->registerAndActivateUser(new \DateTimeImmutable());

        // WHEN
        $this->useCase->execute(
            $user->getEmail(),
            new Password('secret!'),
            new \DateTimeImmutable()
        );
    }
}
