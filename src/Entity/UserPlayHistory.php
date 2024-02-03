<?php

namespace App\Entity;

use App\Repository\UserPlayHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPlayHistoryRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq', columns: ['track_id', 'user_id', 'played_at'])]
class UserPlayHistory implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $playedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Track $track = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayedAt(): ?\DateTimeInterface
    {
        return $this->playedAt;
    }

    public function setPlayedAt(\DateTimeInterface $playedAt): static
    {
        $this->playedAt = $playedAt;

        return $this;
    }

    public function getTrack(): ?Track
    {
        return $this->track;
    }

    public function setTrack(?Track $track): static
    {
        $this->track = $track;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->getUser()->getId(),
            'track' => [
                'id' => $this->getTrack()->getId(),
                'name' => $this->getTrack()->getName(),
            ],
            'played_at' => $this->getPlayedAt()->format('Y-m-d H:i:sp'),
            'played_at_ts' => $this->getPlayedAt()->getTimestamp(),
        ];
    }
}
