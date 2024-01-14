<?php

namespace App\Service;

class SpotifyLoginService
{
    public function __construct(
        protected string $client_id,
        protected string $redirect_uri,
    ) {
    }

    public function getLoginUrl(): string
    {
        // @todo - make permissions configurable
        $permissions = [
            'user-read-private',
            'user-read-email',
            'user-read-recently-played',
        ];

        return 'https://accounts.spotify.com/authorize'.
                '?client_id='.$this->client_id.
                '&response_type=code'.
                '&redirect_uri='.urlencode($this->redirect_uri).
                '&scope='.rawurlencode(join(' ', $permissions));
    }
}
