<?php

declare(strict_types=1);

namespace App\Security\Infrastructure\Console;

use App\Security\Domain\Ports\IHashPasswords;
use App\Security\Domain\UseCase\ForgottenPasswordDeclaration;
use App\Security\Domain\UseCase\PasswordReset;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ResetPassword extends Command
{
    private ForgottenPasswordDeclaration $forgottenPasswordDeclaration;
    private PasswordReset $passwordReset;
    private IHashPasswords $passwordHasher;

    public function __construct(ForgottenPasswordDeclaration $forgottenPasswordDeclaration, PasswordReset $passwordReset, IHashPasswords $passwordHasher)
    {
        parent::__construct('user:reset-password');
        $this->forgottenPasswordDeclaration = $forgottenPasswordDeclaration;
        $this->passwordReset = $passwordReset;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure()
    {
        $this->setHelp('reset a user password for provided email and new password')
            ->addOption('email', 'email', InputOption::VALUE_OPTIONAL, 'provided email - MUST BE VALID')
            ->addOption('password', 'password', InputOption::VALUE_OPTIONAL, 'provided password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $email = $input->getOption('email') ?? $io->ask('Email ?', '');
            $newPassword = $input->getOption('password') ?? $io->ask('New password ?', '');

            $email = new Email($email);
            $password = Password::fromPlainPassword($newPassword, $this->passwordHasher);

            $now = new \DateTimeImmutable();
            $forgottenPasswordUser = $this->forgottenPasswordDeclaration->execute($email, $now);

            $user = $this->passwordReset->execute($forgottenPasswordUser->getToken()->getValue(), $password, $now);

            $io->writeln('Success !');
            $io->writeln('Authenticated User : ');
            $io->writeln((string) $user->getUuid());
            $io->writeln('Authentication Token : ');
            $io->writeln($user->getToken()->getValue());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
