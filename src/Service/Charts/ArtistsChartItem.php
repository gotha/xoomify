<?php

namespace App\Service\Charts;

use App\Entity\Artist;
use App\Entity\User;

class ArtistsChartItem
{
    public function __construct(
        protected Artist $artist,
        protected int $num_plays,
    ) {
    }

    public function getArtist(): Artist
    {
        return $this->artist;
    }

    /**
     * @return User[]
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    public function getNumPlays(): int
    {
        return $this->num_plays;
    }
}
