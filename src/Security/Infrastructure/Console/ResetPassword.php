<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Console;

use App\Security\Domain\ForgottenPasswordDeclaration\UseCase as ForgottenPasswordDeclaration;
use App\Security\Domain\PasswordReset\UseCase as PasswordReset;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Password;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ResetPassword extends Command
{
    private ForgottenPasswordDeclaration $forgottenPasswordDeclaration;
    private PasswordReset $passwordReset;

    public function __construct(ForgottenPasswordDeclaration $forgottenPasswordDeclaration, PasswordReset $passwordReset)
    {
        parent::__construct('user:reset-password');
        $this->forgottenPasswordDeclaration = $forgottenPasswordDeclaration;
        $this->passwordReset = $passwordReset;
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
            $password = new Password($newPassword);

            $declaration = $this->forgottenPasswordDeclaration->execute($email);

            $user = $this->passwordReset->execute($declaration->jsonSerialize()['forgottenPasswordTokenValue'], $password);

            $io->writeln('Success !');
            $io->writeln('Authenticated User : ');
            $io->writeln($user->jsonSerialize());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
