<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $ref = null;

    #[Assert\Length(max:255, maxMessage:"Le commentaire ne peut pas dépasser 255 caractères.")]
    #[Assert\NotBlank(message: 'votre commentaire est vide..')]
    #[ORM\Column(length: 255)]
    private ?string $message = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Annonce $annonce = null;

    public function getRef(): ?int
    {
        return $this->ref;
    }

   

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message= null): static
    {
        $this->message = $message;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAnnonce(): ?Annonce
    {
        return $this->annonce;
    }

    public function setAnnonce(?Annonce $annonce): static
    {
        $this->annonce = $annonce;

        return $this;
    }
}
