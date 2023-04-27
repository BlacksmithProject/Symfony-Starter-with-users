<?php

declare(strict_types=1);

namespace App\Tests\Security\UseCase;

use App\Security\Domain\Exception\EmailIsInvalidOrAlreadyTaken;
use App\Security\Domain\UseCase\Registration;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use App\Security\Domain\ValueObject\TokenType;
use App\Security\Infrastructure\Adapters\BcryptPasswordHandler;
use App\Tests\Security\AbstractTestCase;

final class RegistrationTest extends AbstractTestCase
{
    private Registration $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = $this->getRegistration();
    }

    /** @test */
    public function registration with valid email and password(): void
    {
        // GIVEN
        $email = new Email('eddard.stark@winterfell.north');
        $password = Password::fromPlainPassword('winterIsComing', new BcryptPasswordHandler());

        // WHEN
        $token = $this->useCase->execute($email, $password, new \DateTimeImmutable());

        // THEN
        self::assertSame(TokenType::ACTIVATION, $token->getTokenType());
    }

    /** @test */
    public function cannot register with an already used email(): void
    {
        // EXPECT
        self::expectException(EmailIsInvalidOrAlreadyTaken::class);

        // GIVEN
        $email = new Email('eddard.stark@winterfell.north');
        $password = Password::fromPlainPassword('winterIsComing', new BcryptPasswordHandler());
        $this->useCase->execute($email, $password, new \DateTimeImmutable());

        // WHEN
        $this->useCase->execute($email, $password, new \DateTimeImmutable());
    }
}
