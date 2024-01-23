<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $user_type = null;

    #[ORM\Column(length: 255)]
    private ?string $spotifyUserId = null;

    #[ORM\OneToOne(targetEntity: UserToken::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserToken $token = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserImage::class, orphanRemoval: true)]
    private Collection $image;

    public function __construct()
    {
        $this->image = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserType(): ?string
    {
        return $this->user_type;
    }

    public function setUserType(?string $user_type): static
    {
        $this->user_type = $user_type;

        return $this;
    }

    public function getSpotifyUserId(): ?string
    {
        return $this->spotifyUserId;
    }

    public function setSpotifyUserId(string $spotifyUserId): static
    {
        $this->spotifyUserId = $spotifyUserId;

        return $this;
    }

    public function getToken(): ?UserToken
    {
        return $this->token;
    }

    public function setToken(?UserToken $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, UserImage>
     */
    public function getImage(): Collection
    {
        return $this->image;
    }

    public function getSmallestImage(): UserImage|null
    {
        $images = $this->getImage()->getValues();
        $min = array_shift($images);
        foreach ($images as $img) {
            if (null == $min) {
                $min = $img;
                continue;
            }
            $minRatio = $min->getWidth() * $min->getHeight();
            $imgRatio = $img->getWidth() * $img->getHeight();
            if ($imgRatio < $minRatio) {
                $min = $img;
            }
        }

        return $min;
    }

    public function addImage(UserImage $image): static
    {
        if (!$this->image->contains($image)) {
            $this->image->add($image);
            $image->setUser($this);
        }

        return $this;
    }

    public function removeImage(UserImage $image): static
    {
        if ($this->image->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getUser() === $this) {
                $image->setUser(null);
            }
        }

        return $this;
    }
}
