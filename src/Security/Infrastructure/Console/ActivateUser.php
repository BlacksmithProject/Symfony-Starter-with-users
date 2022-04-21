<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Console;

use App\Security\Domain\Activation\UseCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ActivateUser extends Command
{
    private UseCase $activation;

    public function __construct(UseCase $authentication)
    {
        parent::__construct('user:activate');
        $this->activation = $authentication;
    }

    protected function configure()
    {
        $this->setHelp('Activate a user with its activation token value')
            ->addOption('tokenValue', 'value', InputOption::VALUE_OPTIONAL, 'activation token value');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $tokenValue = $input->getOption('tokenValue') ?? $io->ask('Activation token value ?', '');
            $user = $this->activation->execute($tokenValue);

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
