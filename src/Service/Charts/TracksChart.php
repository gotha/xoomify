<?php

namespace App\Service\Charts;

class TracksChart implements \JsonSerializable
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

    public function jsonSerialize(): array
    {
        return ['items' => $this->getItems()];
    }
}
