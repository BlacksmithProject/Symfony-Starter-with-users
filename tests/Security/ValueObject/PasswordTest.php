<?php
declare(strict_types=1);

namespace App\Tests\Security\ValueObject;

use App\Security\Domain\Exception\PasswordIsTooShort;
use App\Security\Domain\ValueObject\Password;
use App\Security\Infrastructure\Adapters\BcryptPasswordHandler;
use PHPUnit\Framework\TestCase;

final class PasswordTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideTooShortPasswords
     */
    public function password must be at least 6 characters long(string $plainPassword): void
    {
        // SHOULD
        self::expectException(PasswordIsTooShort::class);

        // WHEN
        Password::fromPlainPassword($plainPassword, new BcryptPasswordHandler());
    }

    public function provideTooShortPasswords(): array
    {
        return [
            ['empty' => ''],
            ['1 character' => '1'],
            ['2 characters' => '12'],
            ['3 characters' => '123'],
            ['4 characters' => '1234'],
            ['5 characters' => '12345'],
        ];
    }
    /**
     * @test
     * @dataProvider provideBlankPasswords
     */
    public function password cannot start or end with blank character(string $plainPassword): void
    {
        // SHOULD
        self::expectException(PasswordIsTooShort::class);

        // WHEN
        Password::fromPlainPassword($plainPassword, new BcryptPasswordHandler());
    }

    public function provideBlankPasswords(): array
    {
        return [
            ['6 spaces' => '      '],
            ['start with space' => ' 12345'],
            ['end with space' => '12345 '],
        ];
    }
}
