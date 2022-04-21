<?php
declare(strict_types=1);

namespace App\Security\Domain\PasswordReset\Model;

use App\Security\Domain\Shared\ValueObject\Email;

final class UserToResetPassword
{
    private string $id;
    private string $password;
    private Email $email;
    private \DateTimeImmutable $forgottenPasswordTokenExpirationDate;

    public function __construct(
        string $id,
        string $oldPassword,
        string $email,
        \DateTimeImmutable $forgottenPasswordTokenExpirationDate
    ) {
        $this->id = $id;
        $this->password = $oldPassword;
        $this->email = new Email($email);
        $this->forgottenPasswordTokenExpirationDate = $forgottenPasswordTokenExpirationDate;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function updatePassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }

    public function forgottenPasswordTokenHasExpired(): bool
    {
        return $this->forgottenPasswordTokenExpirationDate < new \DateTimeImmutable();
    }
}
