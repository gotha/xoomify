<?php

namespace App\Service\Charts;

class TracksChart
{
    /**
     * @param TracksChartItem[] $items
     */
    public function __construct(
        protected array $items
    ) {
    }

    /**
     * @return TracksChartItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
