<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Console;

use App\Security\Domain\Activation\UseCase as Activation;
use App\Security\Domain\Registration\UseCase as Registration;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Password;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RegisterUser extends Command
{
    private Registration $registration;
    private Activation $activation;

    public function __construct(Registration $registration, Activation $activation)
    {
        parent::__construct('user:register');
        $this->registration = $registration;
        $this->activation = $activation;
    }

    protected function configure()
    {
        $this->setHelp('Create an activated user with an email and a password')
            ->addOption('email', 'email', InputOption::VALUE_OPTIONAL, 'provided email - MUST BE VALID')
            ->addOption('password', 'pwd', InputOption::VALUE_OPTIONAL, 'provided password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $email = $input->getOption('email') ?? $io->ask('Email ?', '');
            $password = $input->getOption('password') ?? $io->ask('Password ?', '');

            $email = new Email($email);
            $password = new Password($password);

            $user = $this->registration->execute($email, $password);

            $user = $this->activation->execute($user->jsonSerialize()['activationTokenValue']);

            $io->writeln('Success !');
            $io->writeln('Activated User : ');
            $io->writeln($user->jsonSerialize());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
