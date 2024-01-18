<?php

namespace App\Service\Charts;

class ArtistsChart
{
    /**
     * @param ArtistsChartItem[] $items
     */
    public function __construct(
        protected array $items
    ) {
    }

    /**
     * @return ArtistsChartItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
