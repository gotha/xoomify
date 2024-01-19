<?php

namespace App\Service;

use App\Entity\Artist;
use App\Entity\ArtistImage;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;

class ArtistsService
{
    public function __construct(
        protected ArtistRepository $artistRepository,
        protected EntityManagerInterface $em,
        protected SpotifyService $spotifyService,
    ) {
    }

    public function updateArtistInfo(string $artistSpotifyId): void
    {
        $artist = $this->artistRepository->findOneBy(['spotifyId' => $artistSpotifyId]);
        if (!$artist) {
            $artist = new Artist();
            $artist->setSpotifyId($artistSpotifyId);
        }
        $data = $this->spotifyService->getArtist($artistSpotifyId);
        $artist->setName($data->name);

        $images = $artist->getArtistImages();
        foreach ($images as $image) {
            $artist->removeArtistImage($image);
        }

        foreach ($data->images as $i) {
            $img = new ArtistImage();
            $img->setUrl($i->url);
            $img->setWidth($i->width);
            $img->setHeight($i->height);
            $this->em->persist($img);

            $artist->addArtistImage($img);
        }

        $this->em->persist($artist);
        $this->em->flush();
    }
}
