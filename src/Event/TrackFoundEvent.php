<?php

namespace App\Event;

use App\Entity\Track;
use Symfony\Contracts\EventDispatcher\Event;

class TrackFoundEvent extends Event
{
    public const NAME = 'track.found';

    public function __construct(
        protected Track $track,
        protected array $artistSpotifyIds,
    ) {
    }

    public function getTrack(): Track
    {
        return $this->track;
    }

    public function getArtistSpotifyIds(): array
    {
        return $this->artistSpotifyIds;
    }
}
