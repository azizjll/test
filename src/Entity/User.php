<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Nom d'utilisateur est nécessaire")]
    #[Assert\Length([
        'min' => 5,
        'max' => 20,
        'minMessage' => "Votre nom doit être au moins {{ limit }} characters long",
        'maxMessage' => "Votre nom ne peut pas dépasser {{ limit }} characters"
    ])]
    private ?string $Username = null;


    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message:"Email est nécessaire")]
    #[Assert\Email]
    #[Assert\Length([
        'min' => 5,
        'max' => 40,
        'minMessage' => "Votre email doit être au moins {{ limit }} characters long",
        'maxMessage' => "Votre email ne peut pas dépasser {{ limit }} characters"
    ])]
    #[Assert\Regex(
             pattern:"/^[\w\.-]+@([\w-]+\.)+[\w-]{2,4}$/",
             message:"L'email '{{ value }}' n'est pas valide."
    )]
    private ?string $email = null;

    
    
    

    

    #[ORM\Column]
    #[Assert\NotBlank(message:"le role  est nécessaire")]
    private array $roles = [];

    #[ORM\Column(length: 100)]
    private ?string $resetToken = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message:'le mot de passe est nécessaire')]
    #[Assert\Length([
        'min' => 8,
        'max' => 100,
        'minMessage' => "Votre mot de passe doit être au moins {{ limit }} characters long",
        'maxMessage' => "Votre mot de passe ne peut pas dépasser {{ limit }} characters"
    ])]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $is_verified = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message:'le le date  est nécessaire')]  
    private ?\DateTimeInterface $DateNaissance = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:'le neumero  est nécessaire')] 
    #[Assert\Regex(
        pattern:"/^[2|5|9|4]\d{7}$/",
            message:"Le numéro de téléphone doit commencer par 2, 5, 9 ou 4 et contenir exactement 8 chiffres"
    )] 


    private ?string $Numero = null;

    #[ORM\Column(nullable: true)]
    private ?int $Cin = null;

    #[ORM\Column(nullable: true)]
    private ?bool $etat = null;

    #[ORM\Column(length: 255)]
    private ?string $ImageUrl = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $borchureFilename = null;

    #[ORM\OneToMany(targetEntity: Annonce::class, mappedBy: 'User')]
    private Collection $annonces;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'User')]
    private Collection $commentaires;

    public function __construct()
    {
        $this->annonces = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->Username;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }


    public function setResetToken(string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->Username;
    }

    /**
     * @see UserInterface
     */
        public function getRoles(): array
        {
            $roles = $this->roles;
            // guarantee every user at least has ROLE_USER
            $roles[] = 'ROLE_USER';

            return array_unique($roles);
        }

        public function setRoles(array $roles): static
        {
            $this->roles =  array_values(array_unique($roles));

            return $this;
        }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
    /**
     * @Assert\Callback
     */
    public function validatePassword(ExecutionContextInterface $context): void
    {
        // Vérifie si le mot de passe respecte les critères
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $this->password)) {
            $context->buildViolation('Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule et un chiffre')
                ->atPath('password')
                ->addViolation();
        }
    }

    public function getIsVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->DateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $DateNaissance): static
    {
        $this->DateNaissance = $DateNaissance;

        return $this;
    }
    /**
     * @Assert\Callback
     */
    public function validateDateNaissance(ExecutionContextInterface $context)
    {
        // Récupérer la date actuelle
        $dateActuelle = new \DateTime();

        // Vérifier si la date de naissance est supérieure à la date actuelle
        if ($this->DateNaissance > $dateActuelle) {
            $context->buildViolation("La date de naissance ne peut pas être dans le futur.")
                ->atPath('DateNaissance')
                ->addViolation();
        }

        // Vérifier si la date de naissance est antérieure à 1980
        $date1960 = new \DateTime('1960-01-01');
        if ($this->DateNaissance < $date1960) {
            $context->buildViolation("La date de naissance doit être postérieure à 1960.")
                ->atPath('DateNaissance')
                ->addViolation();
        }
    }

    public function getNumero(): ?string
    {
        return $this->Numero;
    }

    public function setNumero(?string $Numero): static
    {
        $this->Numero = $Numero;

        return $this;
    }

    public function getCin(): ?int
    {
        return $this->Cin;
    }

    public function setCin(?int $Cin): static
    {
        $this->Cin = $Cin;

        return $this;
    }

    public function getEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(?bool $etat): self
    {
        $this->etat = $etat;

        return $this;
    }



    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(){
        return null;
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function setUsername(?string $Username): static
    {
        $this->Username = $Username;

        return $this;
    }

    public function getBorchureFilename(): ?string
    {
        return $this->borchureFilename;
    }

    public function setBorchureFilename(string $borchureFilename): static
    {
        $this->borchureFilename = $borchureFilename;

        return $this;
    }
    public function getImageUrl(): ?string
    {
        return $this->ImageUrl;
    }

    public function setImageUrl(string $ImageUrl): static
    {
        $this->ImageUrl = $ImageUrl;

        return $this;
    }

    /**
     * @return Collection<int, Annonce>
     */
    public function getAnnonces(): Collection
    {
        return $this->annonces;
    }

    public function addAnnonce(Annonce $annonce): static
    {
        if (!$this->annonces->contains($annonce)) {
            $this->annonces->add($annonce);
            $annonce->setUser($this);
        }

        return $this;
    }

    public function removeAnnonce(Annonce $annonce): static
    {
        if ($this->annonces->removeElement($annonce)) {
            // set the owning side to null (unless already changed)
            if ($annonce->getUser() === $this) {
                $annonce->setUser(null);
            }
        }

        return $this;
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
            $commentaire->setUser($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUser() === $this) {
                $commentaire->setUser(null);
            }
        }

        return $this;
    }
    
}
