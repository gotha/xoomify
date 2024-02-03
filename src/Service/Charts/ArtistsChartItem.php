<?php

namespace App\Service\Charts;

use App\Entity\Artist;

class ArtistsChartItem implements \JsonSerializable
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

    public function getNumPlays(): int
    {
        return $this->num_plays;
    }

    public function jsonSerialize(): array
    {
        return [
            'artist' => $this->getArtist(),
            'num_plays' => $this->getNumPlays(),
        ];
    }
}
