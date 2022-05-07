<?php
declare(strict_types=1);

namespace App\Tests\Security\UseCase;

use App\Security\Domain\Exception\TokenIsExpired;
use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\UseCase\Activation;
use App\Security\Domain\ValueObject\TokenType;
use App\Tests\Security\AbstractTestCase;

final class ActivationTest extends AbstractTestCase
{
    private Activation $useCase;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = $this->getActivation();
    }

    /** @test */
    public function user should be active and authenticated when activated(): void
    {
        // GIVEN
        $activationToken = $this->registerUser(new \DateTimeImmutable());

        // WHEN
        $user = $this->useCase->execute($activationToken->getValue(), new \DateTimeImmutable());

        // THEN
        self::assertSame(TokenType::AUTHENTICATION, $user->getToken()->getTokenType());
        self::assertTrue($user->isActive());
    }

    /** @test */
    public function user should not be active if token is expired(): void
    {
        // EXPECT
        self::expectException(TokenIsExpired::class);

        // GIVEN
        $activationToken = $this->registerUser((new \DateTimeImmutable())->sub(new \DateInterval('P2D')));

        // WHEN
        $this->useCase->execute($activationToken->getValue(), new \DateTimeImmutable());
    }

    /** @test */
    public function a TokenNotFound is thrown if token value is invalid(): void
    {
        // EXPECT
        self::expectException(TokenNotFound::class);

        // GIVEN
        $activationTokenValue = 'wrong token value';

        // WHEN
        $this->useCase->execute($activationTokenValue, new \DateTimeImmutable());
    }

    /** @test */
    public function a TokenNotFound is thrown if user is already active(): void
    {
        // EXPECT
        self::expectException(TokenNotFound::class);

        // GIVEN
        $activationToken = $this->registerUser(new \DateTimeImmutable());
        $this->useCase->execute($activationToken->getValue(), new \DateTimeImmutable());

        // WHEN
        $this->useCase->execute($activationToken->getValue(), new \DateTimeImmutable());
    }
}
