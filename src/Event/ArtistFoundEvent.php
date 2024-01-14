<?php

namespace App\Event;

use App\Entity\Artist;
use Symfony\Contracts\EventDispatcher\Event;

class ArtistFoundEvent extends Event
{
    public const NAME = 'artist.found';

    public function __construct(
        protected Artist $artist,
    ) {
    }

    public function getArtist(): Artist
    {
        return $this->artist;
    }
}
