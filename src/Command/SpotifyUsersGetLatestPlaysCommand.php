<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'spotify:users:get-latest-plays',
    description: 'Fetch latest play history information for all users',
)]
class SpotifyUsersGetLatestPlaysCommand extends Command
{
    public function __construct(
        protected UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $consolePath = realpath(__DIR__.'/../../bin/console');

        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $io->info(sprintf('processing user ID: %s: %s', $user->getId(), $user->getName()));

            $process = new Process(['php', $consolePath, 'spotify:user:get-latest-plays', $user->getId(), '-vvv']);
            $process->setTimeout(3600);

            $process->run(function ($type, $buffer) {
                echo $buffer;
            });
        }

        $io->success('done');

        return Command::SUCCESS;
    }
}
