<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class TrackPlayedEvent extends Event
{
    public const NAME = 'track.played';

    public function __construct(
        protected User $user,
        protected string $trackSpotifyId,
        protected \DateTime $playedAt,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTrackSpotifyId(): string
    {
        return $this->trackSpotifyId;
    }

    public function getPlayedAt(): \DateTime
    {
        return $this->playedAt;
    }
}
