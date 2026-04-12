<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'document')]
#[ORM\HasLifecycleCallbacks]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom du document est obligatoire')]
    #[Assert\Length(max: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type de document est obligatoire')]
    private ?string $typeDocument = null;

    #[ORM\Column(length: 255)]
    private ?string $cheminFichier = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $taille = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateUpload = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\Column(length: 20)]
    private string $statut = 'en_attente';

    public function __construct()
    {
        $this->dateUpload = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setDateUploadValue(): void
    {
        if ($this->dateUpload === null) {
            $this->dateUpload = new \DateTime();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getTypeDocument(): ?string
    {
        return $this->typeDocument;
    }

    public function setTypeDocument(string $typeDocument): self
    {
        $this->typeDocument = $typeDocument;
        return $this;
    }

    public function getCheminFichier(): ?string
    {
        return $this->cheminFichier;
    }

    public function setCheminFichier(string $cheminFichier): self
    {
        $this->cheminFichier = $cheminFichier;
        return $this;
    }

    public function getTaille(): ?int
    {
        return $this->taille;
    }

    public function setTaille(?int $taille): self
    {
        $this->taille = $taille;
        return $this;
    }

    public function getDateUpload(): ?\DateTimeInterface
    {
        return $this->dateUpload;
    }

    public function setDateUpload(\DateTimeInterface $dateUpload): self
    {
        $this->dateUpload = $dateUpload;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getStatutBadgeClass(): string
    {
        return match($this->statut) {
            'valide' => 'success',
            'rejete' => 'danger',
            'en_attente' => 'warning',
            default => 'secondary'
        };
    }
}