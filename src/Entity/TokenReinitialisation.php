<?php

namespace App\Entity;

use App\Repository\TokenReinitialisationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TokenReinitialisationRepository::class)]
#[ORM\Table(name: 'tokenreinitialisation')]
#[ORM\HasLifecycleCallbacks]
class TokenReinitialisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'tokensReinitialisation')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $token = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\Column(type: 'boolean')]
    private bool $utilise = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateUtilisation = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setCreationValue(): void
    {
        if ($this->dateCreation === null) {
            $this->dateCreation = new \DateTime();
        }
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }

    public function isUtilise(): bool
    {
        return $this->utilise;
    }

    public function setUtilise(bool $utilise): self
    {
        $this->utilise = $utilise;
        if ($utilise && $this->dateUtilisation === null) {
            $this->dateUtilisation = new \DateTime();
        }
        return $this;
    }

    public function getDateUtilisation(): ?\DateTimeInterface
    {
        return $this->dateUtilisation;
    }

    public function setDateUtilisation(?\DateTimeInterface $dateUtilisation): self
    {
        $this->dateUtilisation = $dateUtilisation;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function isValide(): bool
    {
        return !$this->utilise && $this->dateExpiration > new \DateTime();
    }
}
