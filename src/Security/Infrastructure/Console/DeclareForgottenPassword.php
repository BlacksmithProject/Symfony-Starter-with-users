<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Console;

use App\Security\Domain\ForgottenPasswordDeclaration\UseCase;
use App\Security\Domain\Shared\ValueObject\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DeclareForgottenPassword extends Command
{
    private UseCase $forgottenPasswordDeclaration;

    public function __construct(UseCase $forgottenPasswordDeclaration)
    {
        parent::__construct('user:declare-forgotten-password');
        $this->forgottenPasswordDeclaration = $forgottenPasswordDeclaration;
    }

    protected function configure()
    {
        $this->setHelp('Declare a forgotten password for a provided email')
            ->addOption('email', 'email', InputOption::VALUE_OPTIONAL, 'provided email - MUST BE VALID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $email = $input->getOption('email') ?? $io->ask('Email ?', '');

            $email = new Email($email);

            $declaration = $this->forgottenPasswordDeclaration->execute($email);

            $io->writeln('Success !');
            $io->writeln('Forgotten password Declaration : ');
            $io->writeln($declaration->jsonSerialize());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
