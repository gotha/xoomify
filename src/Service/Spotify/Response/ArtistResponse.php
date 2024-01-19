<?php

namespace App\Service\Spotify\Response;

class ArtistResponse
{
    /* @var ExternalUrl[] $externalUrls */
    public array $externalUrls;
    /* @var Follower[] $followers */
    public array $followers;
    public array $genres;
    public string $href;
    public string $id;
    /** @var Image[] */
    public array $images;
    public string $name;
    public int $popularity;
    public string $type;
    public string $uri;
}

class Follower
{
    public string $href;
    public int $total;
}
