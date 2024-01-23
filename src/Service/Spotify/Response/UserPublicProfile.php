<?php

namespace App\Service\Spotify\Response;

class UserPublicProfile
{
    public string $id;
    public string $display_name;
    public string $href;
    /** @var Image[] */
    public array $images;
    public string $uri;
}
