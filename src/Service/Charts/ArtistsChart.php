<?php

namespace App\Service\Charts;

class ArtistsChart implements \JsonSerializable
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

    public function jsonSerialize(): array
    {
        return ['items' => $this->getItems()];
    }
}
