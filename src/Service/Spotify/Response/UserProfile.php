<?php

namespace App\Service\Spotify\Response;

class UserProfile
{
    public string $country;
    public string $display_name;
    public string $email;
    public string $href;
    public string $id;
    /** @var UserProfileImages[] */
    public array $images;
    public string $product;
    public string $type;
    public string $uri;
}

class UserProfileImages
{
    public string $url;
    public int $height;
    public int $width;
}
