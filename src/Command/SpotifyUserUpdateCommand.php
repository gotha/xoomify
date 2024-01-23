<?php

namespace App\Command;

use App\Entity\UserImage;
use App\Repository\UserRepository;
use App\Service\SpotifyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'spotify:user:update',
    description: 'update user information from public profile',
)]
class SpotifyUserUpdateCommand extends Command
{
    public function __construct(
        protected SpotifyService $spotifyService,
        protected UserRepository $userRepository,
        protected EntityManagerInterface $em,
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

        foreach ($user->getImage() as $img) {
            $this->em->remove($img);
        }

        foreach ($profile->images as $img) {
            $i = new UserImage();
            $i->setUrl($img->url);
            $i->setWidth($img->width);
            $i->setHeight($img->height);
            $user->addImage($i);
            $this->em->persist($i);
        }

        $user->setName($profile->display_name);

        $this->em->persist($user);
        $this->em->flush();

        $io->success('done');

        return Command::SUCCESS;
    }
}
