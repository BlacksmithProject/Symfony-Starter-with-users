<?php

declare(strict_types=1);

namespace App\Security\Infrastructure\Console;

use App\Security\Domain\Ports\IHashPasswords;
use App\Security\Domain\UseCase\Activation;
use App\Security\Domain\UseCase\Registration;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RegisterUser extends Command
{
    private Registration $registration;
    private Activation $activation;
    private IHashPasswords $passwordHasher;

    public function __construct(Registration $registration, Activation $activation, IHashPasswords $passwordHasher)
    {
        parent::__construct('user:register');
        $this->registration = $registration;
        $this->activation = $activation;
        $this->passwordHasher = $passwordHasher;
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
            $password = Password::fromPlainPassword($password, $this->passwordHasher);

            $now = new \DateTimeImmutable();
            $activationToken = $this->registration->execute($email, $password, $now);

            $user = $this->activation->execute($activationToken->getValue(), $now);

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
