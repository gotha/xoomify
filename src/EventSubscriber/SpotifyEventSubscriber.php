<?php

namespace App\EventSubscriber;

use App\Entity\UserPlayHistory;
use App\Event\ArtistFoundEvent;
use App\Event\TrackFoundEvent;
use App\Event\TrackPlayedEvent;
use App\Repository\ArtistRepository;
use App\Repository\TrackRepository;
use App\Repository\UserPlayHistoryRepository;
use App\Service\ArtistsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SpotifyEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected ArtistRepository $artistRepository,
        protected TrackRepository $trackRepository,
        protected UserPlayHistoryRepository $userPlayHistoryRepository,
        protected EntityManagerInterface $em,
        protected ArtistsService $artistsService,
    ) {
    }

    public function onArtistFound(ArtistFoundEvent $e): void
    {
        $this->artistsService->updateArtistInfo($e->getArtist()->getSpotifyId());
    }

    public function onTrackFound(TrackFoundEvent $e): void
    {
        $track = $e->getTrack();
        $existingTrack = $this->trackRepository->findOneBy(['spotifyId' => $track->getSpotifyId()]);
        if ($existingTrack) {
            return;
        }
        $artists = $this->artistRepository->findBy(['spotifyId' => $e->getArtistSpotifyIds()]);
        foreach ($artists as $a) {
            $track->addArtist($a);
        }

        $this->em->persist($track);
        $this->em->flush();
    }

    public function onTrackPlayed(TrackPlayedEvent $e): void
    {
        $track = $this->trackRepository->findOneBy(['spotifyId' => $e->getTrackSpotifyId()]);

        $exists = $this->userPlayHistoryRepository->findOneBy([
            'user' => $e->getUser(),
            'track' => $track,
            'playedAt' => $e->getPlayedAt(),
        ]);

        if ($exists) {
            return;
        }

        $uph = new UserPlayHistory();
        $uph->setPlayedAt($e->getPlayedAt());
        $uph->setUser($e->getUser());
        $uph->setTrack($track);

        $this->em->persist($uph);
        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ArtistFoundEvent::NAME => 'onArtistFound',
            TrackFoundEvent::NAME => 'onTrackFound',
            TrackPlayedEvent::NAME => 'onTrackPlayed',
        ];
    }
}
