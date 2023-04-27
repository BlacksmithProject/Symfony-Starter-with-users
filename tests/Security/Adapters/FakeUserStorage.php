<?php

declare(strict_types=1);

namespace App\Tests\Security\Adapters;

use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\Exception\UserNotFound;
use App\Security\Domain\Model\User;
use App\Security\Domain\Ports\IProvideUsers;
use App\Security\Domain\Ports\IStoreUsers;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\TokenType;
use Symfony\Component\Uid\Uuid;

final class FakeUserStorage implements IStoreUsers, IProvideUsers
{
    public static array $users;

    private FakeTokenStorage $tokenStorage;

    public function __construct(FakeTokenStorage $tokenStorage)
    {
        self::$users = [];
        $this->tokenStorage = $tokenStorage;
    }

    public function getUserToActivate(Uuid $userId): User
    {
        return $this->getByIdAndTokenType($userId, TokenType::ACTIVATION);
    }

    public function getActivatedUser(Email $email): User
    {
        $userId = null;
        foreach (self::$users as $userData) {
            if ($email->equals($userData['email'])) {
                $userId = $userData['id'];
            }
        }
        if ($userId === null) {
            throw new UserNotFound();
        }

        return $this->getByIdAndTokenType($userId, TokenType::AUTHENTICATION);
    }

    public function getForgottenPasswordUser(Uuid $userId): User
    {
        return $this->getByIdAndTokenType($userId, TokenType::FORGOTTEN_PASSWORD);
    }

    public function isEmailAlreadyUsed(Email $email): bool
    {
        foreach (self::$users as $userData) {
            if ($email->equals($userData['email'])) {
                return true;
            }
        }

        return false;
    }

    public function add(User $user): void
    {
        $this->save($user);
        $this->tokenStorage->save($user->getToken());
    }

    public function activate(User $user): void
    {
        $this->save(new User($user->getUuid(), $user->getEmail(), $user->getPassword(), true, $user->getToken()));
        $this->tokenStorage->remove($user->getUuid(), TokenType::ACTIVATION);
        $this->tokenStorage->save($user->getToken());
    }

    public function renewForgottenPassword(User $user): void
    {
        $this->tokenStorage->remove($user->getUuid(), TokenType::FORGOTTEN_PASSWORD);
        $this->tokenStorage->save($user->getToken());
    }

    public function updatePassword(User $user): void
    {
        $this->save($user);
        $this->tokenStorage->remove($user->getUuid(), TokenType::FORGOTTEN_PASSWORD);
        $this->tokenStorage->save($user->getToken());
    }

    private function save(User $user): void
    {
        self::$users[$user->getUuid()->jsonSerialize()] = [
            'id' => $user->getUuid(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'isActive' => $user->isActive(),
        ];
    }

    /**
     * @throws TokenNotFound
     * @throws UserNotFound
     */
    private function getByIdAndTokenType(Uuid $userId, TokenType $tokenType): User
    {
        $token = $this->tokenStorage->getToken($userId, $tokenType);

        if (!isset(self::$users[$userId->jsonSerialize()])) {
            throw new UserNotFound();
        }

        $userData = self::$users[$userId->jsonSerialize()];

        return new User(
            $userData['id'],
            $userData['email'],
            $userData['password'],
            $userData['isActive'],
            $token
        );
    }
}
