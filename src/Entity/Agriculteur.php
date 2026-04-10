<?php

namespace App\Entity;

use App\Repository\AgriculteurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AgriculteurRepository::class)]
#[ORM\Table(name: 'agriculteur')]
class Agriculteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'agriculteur', targetEntity: Utilisateur::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $adresseferme = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'La superficie doit être positive')]
    private ?string $superficieferme = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $typeCulture = null;

    #[ORM\Column(length: 50)]
    private string $statuscompte = 'en_attente';

    #[ORM\Column(type: 'boolean')]
    private bool $compteverifie = false;

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

    public function getAdresseferme(): ?string
    {
        return $this->adresseferme;
    }

    public function setAdresseferme(?string $adresseferme): self
    {
        $this->adresseferme = $adresseferme;
        return $this;
    }

    public function getSuperficieferme(): ?string
    {
        return $this->superficieferme;
    }

    public function setSuperficieferme(?string $superficieferme): self
    {
        $this->superficieferme = $superficieferme;
        return $this;
    }

    public function getTypeCulture(): ?string
    {
        return $this->typeCulture;
    }

    public function setTypeCulture(?string $typeCulture): self
    {
        $this->typeCulture = $typeCulture;
        return $this;
    }

    public function getStatuscompte(): string
    {
        return $this->statuscompte;
    }

    public function setStatuscompte(string $statuscompte): self
    {
        $this->statuscompte = $statuscompte;
        return $this;
    }

    public function isCompteverifie(): bool
    {
        return $this->compteverifie;
    }

    public function setCompteverifie(bool $compteverifie): self
    {
        $this->compteverifie = $compteverifie;
        return $this;
    }
}