<?php

namespace App\Command;

use App\Entity\Artist;
use App\Entity\Track;
use App\Event\ArtistFoundEvent;
use App\Event\TrackFoundEvent;
use App\Event\TrackPlayedEvent;
use App\Repository\ArtistRepository;
use App\Repository\TrackRepository;
use App\Repository\UserPlayHistoryRepository;
use App\Repository\UserRepository;
use App\Service\SpotifyPersistedUserTokenService;
use App\Service\SpotifyService;
use App\Service\SpotifyTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'spotify:user:get-latest-plays',
    description: 'Fetch the latest plays for specific user and saves them in the database',
)]
class SpotifyUserGetLatestPlaysCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private ArtistRepository $artistRepository,
        private TrackRepository $trackRepository,
        private UserPlayHistoryRepository $userPlayHistoryRepository,
        private EntityManagerInterface $em,
        private SpotifyService $spotify,
        private SpotifyTokenService $spotifyTokenService,
        private SpotifyPersistedUserTokenService $spotifyPersistedUserTokenService,
        private EventDispatcherInterface $eventDispatcher,
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

        if ($userId) {
            $io->note(sprintf('getting data for user: %s', $userId));
        }

        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (!$user) {
            $io->error(sprintf('User with id %d not found', $userId));

            return Command::FAILURE;
        }
        $io->note(sprintf('user: %s found', $user->getName()));

        $accessToken = null;
        try {
            $token = $this->spotifyPersistedUserTokenService->getToken($user);
            $accessToken = $token->getAccessToken();
        } catch (EntityNotFoundException $e) {
            $io->error('user does not have access token; they must authenticate first');

            return Command::FAILURE;
        }

        $afterDate = (new \DateTime('now', new \DateTimeZone('UTC')));
        $afterDate = $afterDate->sub(new \DateInterval('P1D'));
        $afterTimestamp = $afterDate->getTimestamp() * 1000;

        $lastPlayedSong = $this->userPlayHistoryRepository->getUserLatestPlay($user);
        if ($lastPlayedSong) {
            $afterTimestamp = $lastPlayedSong->getPlayedAt()->getTimestamp() * 1000;
        }

        do {
            $io->info(sprintf('fetching user play history since: %s', date('Y-m-d H:i:s', $afterTimestamp / 1000)));
            $history = $this->spotify->getRecentlyPlayedSongs($accessToken, $afterTimestamp);
            $io->info(sprintf('%s items found', count($history->items)));

            $artists = $history->getArtists();
            foreach ($artists as $a) {
                $artist = new Artist();
                $artist->setSpotifyId($a->id);
                $artist->setName($a->name);
                $this->eventDispatcher->dispatch(new ArtistFoundEvent($artist), ArtistFoundEvent::NAME);
            }

            foreach ($history->items as $item) {
                $track = new Track();
                $track->setSpotifyId($item->track->id);
                $track->setName($item->track->name);
                $track->setDurationMs($item->track->duration_ms);

                $artisSpotifyIds = [];
                foreach ($item->track->artists as $a) {
                    $artisSpotifyIds[] = $a->id;
                }
                $this->eventDispatcher->dispatch(new TrackFoundEvent($track, $artisSpotifyIds), TrackFoundEvent::NAME);
            }

            foreach ($history->items as $item) {
                $playedAt = new \DateTime($item->played_at, new \DateTimeZone('UTC'));
                $this->eventDispatcher->dispatch(new TrackPlayedEvent($user, $item->track->id, $playedAt), TrackPlayedEvent::NAME);
            }

            $afterTimestamp = $history->getNextAfter();
        } while ($afterTimestamp && count($history->items) > 0);

        $io->info('done');

        return Command::SUCCESS;
    }
}
