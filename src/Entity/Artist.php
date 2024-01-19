<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $spotifyId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Track::class, mappedBy: 'artist')]
    private Collection $tracks;

    #[ORM\OneToMany(mappedBy: 'Artist', targetEntity: ArtistImage::class, orphanRemoval: true)]
    private Collection $artistImages;

    public function __construct()
    {
        $this->tracks = new ArrayCollection();
        $this->artistImages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpotifyId(): ?string
    {
        return $this->spotifyId;
    }

    public function setSpotifyId(string $spotifyId): static
    {
        $this->spotifyId = $spotifyId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Track>
     */
    public function getTracks(): Collection
    {
        return $this->tracks;
    }

    public function addTrack(Track $track): static
    {
        if (!$this->tracks->contains($track)) {
            $this->tracks->add($track);
            $track->addArtist($this);
        }

        return $this;
    }

    public function removeTrack(Track $track): static
    {
        if ($this->tracks->removeElement($track)) {
            $track->removeArtist($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ArtistImage>
     */
    public function getArtistImages(): Collection
    {
        return $this->artistImages;
    }

    public function addArtistImage(ArtistImage $artistImage): static
    {
        if (!$this->artistImages->contains($artistImage)) {
            $this->artistImages->add($artistImage);
            $artistImage->setArtist($this);
        }

        return $this;
    }

    public function removeArtistImage(ArtistImage $artistImage): static
    {
        if ($this->artistImages->removeElement($artistImage)) {
            // set the owning side to null (unless already changed)
            if ($artistImage->getArtist() === $this) {
                $artistImage->setArtist(null);
            }
        }

        return $this;
    }
}
