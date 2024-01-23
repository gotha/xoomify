<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\SpotifyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'spotify:user:get',
    description: 'fetches and displays information about user from Spotify\'s API',
)]
class SpotifyUserGetCommand extends Command
{
    public function __construct(
        protected SpotifyService $spotifyService,
        protected UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'ID of the user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('userId');

        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (!$user) {
            $io->error('user with such id not found');

            return Command::FAILURE;
        }

        $profile = $this->spotifyService->getUserProfile($user->getSpotifyUserId());

        echo json_encode($profile);

        return Command::SUCCESS;
    }
}
