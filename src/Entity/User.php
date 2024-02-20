<?php

namespace App\Entity;

use App\Repository\UserRepository;
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
    #[Assert\Email(message:"The email '{{ value }}' is not a valid email ")]
    #[Assert\Length([
        'min' => 5,
        'max' => 40,
        'minMessage' => "Votre email doit être au moins {{ limit }} characters long",
        'maxMessage' => "Votre email ne peut pas dépasser {{ limit }} characters"
    ])]
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
        'min' => 5,
        'max' => 10,
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
    private ?string $Numero = null;

    #[ORM\Column(nullable: true)]
    private ?int $Cin = null;

    #[ORM\Column(nullable: true)]
    private ?bool $etat = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $borchureFilename = null;
    

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
    
}
