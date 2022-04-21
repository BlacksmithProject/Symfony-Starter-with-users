<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Console;

use App\Security\Domain\Authentication\UseCase;
use App\Security\Domain\Shared\ValueObject\Email;
use App\Security\Domain\Shared\ValueObject\Password;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AuthenticateUser extends Command
{
    private UseCase $authentication;

    public function __construct(UseCase $authentication)
    {
        parent::__construct('user:authenticate');
        $this->authentication = $authentication;
    }

    protected function configure()
    {
        $this->setHelp('Authenticate a user with its email and password')
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

            $user = $this->authentication->execute($email, $password);

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
