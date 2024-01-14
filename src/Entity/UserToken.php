<?php

namespace App\Entity;

use App\Repository\UserTokenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserTokenRepository::class)]
class UserToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $scope = null;

    #[ORM\Column(nullable: true)]
    private ?int $expiresIn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\OneToOne(targetEntity: User::class, mappedBy: 'token')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    public function setExpiresIn(?int $expiresIn): static
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeInterface $dateCreated): static
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if (null === $user && null !== $this->user) {
            $this->user->setToken(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $user && $user->getToken() !== $this) {
            $user->setToken($this);
        }

        $this->user = $user;

        return $this;
    }
}
