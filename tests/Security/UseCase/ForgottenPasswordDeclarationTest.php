<?php

declare(strict_types=1);

namespace App\Tests\Security\UseCase;

use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\UseCase\ForgottenPasswordDeclaration;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\TokenType;
use App\Tests\Security\AbstractTestCase;

final class ForgottenPasswordDeclarationTest extends AbstractTestCase
{
    private ForgottenPasswordDeclaration $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = $this->getForgottenPasswordDeclaration();
    }

    /** @test */
    public function user should be able to declare forgotten password(): void
    {
        // GIVEN
        $user = $this->registerAndActivateUser(new \DateTimeImmutable());

        // WHEN
        $userWithForgottenPasswordToken = $this->useCase->execute($user->getEmail(), new \DateTimeImmutable());

        // THEN
        self::assertSame(TokenType::FORGOTTEN_PASSWORD, $userWithForgottenPasswordToken->getToken()->getTokenType());
    }

    /** @test */
    public function forgotten password declaration can only be made by registered user(): void
    {
        // EXPECT
        self::expectException(UserNotFound::class);

        // WHEN
        $this->useCase->execute(new Email('john.doe@example.com'), new \DateTimeImmutable());
    }
}
