<?php

namespace App\Service\Charts;

use App\Entity\Track;

class TracksChartItem
{
    public function __construct(
        protected Track $track,
        protected int $num_plays,
    ) {
    }

    public function getTrack(): Track
    {
        return $this->track;
    }

    public function getNumPlays(): int
    {
        return $this->num_plays;
    }
}
