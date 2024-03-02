<?php

namespace App\Entity;

use App\Repository\AnnonceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

//for controle saisie
use Symfony\Component\Validator\Constraints as Assert;
//for dateand time
use Doctrine\DBAL\Types\Types;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: AnnonceRepository::class)]
//#[ORM\Table(name: 'joueur', indexes: [new Index(columns: ['nom', 'prenom', 'email', 'ign'], flags: ['fulltext'])])]
#[ORM\Index(name: 'annonce', columns: ['titre', 'description'], flags: ['fulltext'])]
class Annonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Length(max:100, maxMessage:"Le titre ne doit pas dépasser 100 caractères.")]
    #[Assert\NotBlank(message: 'veuillez saisir un titre.')]
    #[ORM\Column(length: 255)]
    #[NoBadWords]
    private ?string $titre = null;

    #[Assert\NotBlank(message: 'Veuillez saisir une description.')]
    #[Assert\Length(max:255, maxMessage:"Le commentaire ne doit pas dépasser 255 caractères.")]
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: 'annonce', targetEntity: Reponse::class , cascade:["all"],orphanRemoval:true)]
    
    private Collection $reponses;

    #[ORM\OneToMany(mappedBy: 'annonce', targetEntity: Commentaire::class, orphanRemoval: true)]
    private Collection $commentaires;

   //upload images
    #[ORM\Column(length:255,nullable:true)]
    private ?string $brochureFilename = null;

    #[ORM\ManyToOne(inversedBy: 'annonces')]
    private ?User $User = null;

    public function getBrochureFilename(): ?string
    {
        return $this->brochureFilename;
    }

    public function setBrochureFilename(string $brochureFilename): static
    {
        $this->brochureFilename = $brochureFilename;

        return $this;
    }

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre = null): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description= null): static
    {
        $this->description = $description;

        return $this;
    }

    //date and time 
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setAnnonce($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getAnnonce() === $this) {
                $reponse->setAnnonce(null);
            }
        }

        return $this;
    }

    // App\Entity\Annonce

public function __toString()
{
    return $this->titre; 
}

/**
 * @return Collection<int, Commentaire>
 */
public function getCommentaires(): Collection
{
    return $this->commentaires;
}

public function addCommentaire(Commentaire $commentaire): static
{
    if (!$this->commentaires->contains($commentaire)) {
        $this->commentaires->add($commentaire);
        $commentaire->setAnnonce($this);
    }

    return $this;
}

public function removeCommentaire(Commentaire $commentaire): static
{
    if ($this->commentaires->removeElement($commentaire)) {
        // set the owning side to null (unless already changed)
        if ($commentaire->getAnnonce() === $this) {
            $commentaire->setAnnonce(null);
        }
    }

    return $this;
}

public function getUser(): ?User
{
    return $this->User;
}

public function setUser(?User $User): static
{
    $this->User = $User;

    return $this;
}

}
