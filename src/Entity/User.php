<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $username;

    #[ORM\Column(type: 'integer')]
    private $vk_id;

    #[ORM\Column(type: 'string', length: 255)]
    private $photo_uri;

    #[ORM\Column(type: 'string', length: 255)]
    private $vk_code;

    #[ORM\Column(type: 'string', length: 255)]
    private $vk_access_token;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getVkId(): ?int
    {
        return $this->vk_id;
    }

    public function setVkId(int $vk_id): self
    {
        $this->vk_id = $vk_id;

        return $this;
    }

    public function getPhotoUri(): ?string
    {
        return $this->photo_uri;
    }

    public function setPhotoUri(string $photo_uri): self
    {
        $this->photo_uri = $photo_uri;

        return $this;
    }

    public function getVkCode(): ?string
    {
        return $this->vk_code;
    }

    public function setVkCode(string $vk_code): self
    {
        $this->vk_code = $vk_code;

        return $this;
    }

    public function getVkAccessToken(): ?string
    {
        return $this->vk_access_token;
    }

    public function setVkAccessToken(string $vk_access_token): self
    {
        $this->vk_access_token = $vk_access_token;

        return $this;
    }
}
