<?php
declare(strict_types=1);

namespace App\Tests\Security\Adapters;

use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\Ports\IProvideTokens;
use App\Security\Domain\Ports\IStoreTokens;
use App\Security\Domain\ValueObject\Token;
use App\Security\Domain\ValueObject\TokenType;
use Symfony\Component\Uid\Uuid;

final class FakeTokenStorage implements IStoreTokens, IProvideTokens
{
    /** @var array<string, array<string, Token>> */
    public static array $tokens;

    public function __construct()
    {
        self::$tokens = [];
    }

    public function getToken(Uuid $userId, TokenType $tokenType): Token
    {
        return self::$tokens[$userId->jsonSerialize()][$tokenType->value];
    }

    public function getTokenByValue(string $value, TokenType $tokenType): Token
    {
        foreach (self::$tokens as $tokenData) {
            if (isset($tokenData[$tokenType->value]) && $tokenData[$tokenType->value]->getValue() === $value) {
                return $tokenData[$tokenType->value];
            }
        }

        throw new TokenNotFound();
    }

    public function renew(Token $token): void
    {
        $this->remove($token->getUserId(), $token->getTokenType());
        $this->save($token);
    }

    public function save(Token $token): void
    {
        self::$tokens[$token->getUserId()->jsonSerialize()][$token->getTokenType()->value] = $token;
    }

    public function remove(Uuid $userId, TokenType $tokenType): void
    {
        unset(self::$tokens[$userId->jsonSerialize()][$tokenType->value]);
    }
}
