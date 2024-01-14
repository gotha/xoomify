<?php

namespace App\Service\Spotify\Response;

class PlayHistoryResponse
{
    /** @var SongItem[] */
    public ?array $items;
    public ?string $next;
    public ?Cursors $cursors;
    public ?int $limit;
    public ?string $href;

    /**
     * @return Artists[]
     */
    public function getArtists(): array
    {
        $artists = [];
        foreach ($this->items as $item) {
            foreach ($item->track->artists as $artist) {
                $artists[] = $artist;
            }
        }

        return array_unique($artists, SORT_REGULAR);
    }

    public function getNextAfter(): string|null
    {
        // ex: https://api.spotify.com/v1/me/player/recently-played?after=1705398861357&limit=20
        if (!$this->next) {
            return null;
        }
        $url_items = parse_url($this->next);
        $query_params = [];
        parse_str($url_items['query'], $query_params);

        return $query_params['after'];
    }
}

class SongItem
{
    public Track $track;
    public string $played_at;
    public ?Context $context;
}

class Track
{
    public Album $album;
    /** @var Artists[] */
    public array $artists;
    /** @var string[] */
    public array $available_markets;
    public int $disc_number;
    public int $duration_ms;
    public bool $explicit;
    public ExternalIds $external_ids;
    public ExternalUrls $external_urls;
    public string $href;
    public string $id;
    public bool $is_local;
    public string $name;
    public int $popularity;
    public string $preview_url;
    public int $track_number;
    public string $type;
    public string $uri;
}

class Album
{
    public string $album_type;
    /** @var Artists[] */
    public array $artists;
    /** @var string[] */
    public array $available_markets;
    public ExternalUrls $external_urls;
    public string $href;
    public string $id;
    /** @var Images[] */
    public array $images;
    public string $name;
    public string $release_date;
    public string $release_date_precision;
    public int $total_tracks;
    public string $type;
    public string $uri;
}

class Artists
{
    public ExternalUrls $external_urls;
    public string $href;
    public string $id;
    public string $name;
    public string $type;
    public string $uri;
}

class ExternalUrls
{
    public string $spotify;
}

class Images
{
    public int $height;
    public string $url;
    public int $width;
}

class ExternalIds
{
    public string $isrc;
}

class Context
{
    public string $type;
    public string $href;
    public ExternalUrls $external_urls;
    public string $uri;
}

class Cursors
{
    public string $after;
    public string $before;
}
