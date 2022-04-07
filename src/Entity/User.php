<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

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
    private $vkId;

    #[ORM\Column(type: 'string', length: 255)]
    private $photoUri;

    #[ORM\Column(type: 'string', length: 255)]
    private $vkCode = '';

    #[ORM\Column(type: 'string', length: 255)]
    private $vkAccessToken = '';

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Question::class)]
    private $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

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
        return $this->vkId;
    }

    public function setVkId(int $vkId): self
    {
        $this->vkId = $vkId;

        return $this;
    }

    public function getPhotoUri(): ?string
    {
        return $this->photoUri;
    }

    public function setPhotoUri(string $photoUri): self
    {
        $this->photoUri = $photoUri;

        return $this;
    }

    public function getVkCode(): ?string
    {
        return $this->vkCode;
    }

    public function setVkCode(string $vkCode): self
    {
        $this->vkCode = $vkCode;

        return $this;
    }

    public function getVkAccessToken(): ?string
    {
        return $this->vkAccessToken;
    }

    public function setVkAccessToken(string $vkAccessToken): self
    {
        $this->vkAccessToken = $vkAccessToken;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setAuthor($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getAuthor() === $this) {
                $question->setAuthor(null);
            }
        }

        return $this;
    }
}
