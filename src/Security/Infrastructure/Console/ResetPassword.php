<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Console;

use App\Security\Domain\PasswordReset\UseCase;
use App\Security\Domain\Shared\ValueObject\Password;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ResetPassword extends Command
{
    private UseCase $passwordReset;

    public function __construct(UseCase $passwordReset)
    {
        parent::__construct('user:reset-password');
        $this->passwordReset = $passwordReset;
    }

    protected function configure()
    {
        $this->setHelp('reset a user password for a provided forgottenPassword token')
            ->addOption('tokenValue', 'value', InputOption::VALUE_OPTIONAL, 'forgottenPassword token value')
            ->addOption('password', 'password', InputOption::VALUE_OPTIONAL, 'provided password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $tokenValue = $input->getOption('tokenValue') ?? $io->ask('ForgottenPassword token value ?', '');
            $newPassword = $input->getOption('password') ?? $io->ask('New password ?', '');

            $password = new Password($newPassword);

            $declaration = $this->passwordReset->execute($tokenValue, $password);

            $io->writeln('Success !');
            $io->writeln('Authenticated User : ');
            $io->writeln($declaration->jsonSerialize());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
