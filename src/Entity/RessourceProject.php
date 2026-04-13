<?php

namespace App\Entity;

use App\Repository\RessourceProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RessourceProjectRepository::class)]
#[ORM\Table(name: 'ressourceproject')]
class RessourceProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idressource')]
    private ?int $idressource = null;

    #[ORM\Column(name: 'nomressource', length: 100)]
    private ?string $nomressource = null;

    #[ORM\Column(name: 'typeressource', columnDefinition: "ENUM('equipement','materiaux','service') NOT NULL")]
    private ?string $typeressource = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $cout = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fournisseur = null;

    #[ORM\Column(columnDefinition: "ENUM('prevu','achete') DEFAULT 'prevu'")]
    private ?string $statut = null;

    #[ORM\Column(name: 'dateajout', type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateajout = null;

    #[ORM\ManyToOne(targetEntity: ProjectAgricole::class, inversedBy: 'ressources')]
    #[ORM\JoinColumn(name: 'idproject', referencedColumnName: 'idproject', nullable: false)]
    private ?ProjectAgricole $project = null;

    public function __construct()
    {
        $this->dateajout = new \DateTime();
        $this->statut = 'prevu';
    }

    public function getIdressource(): ?int { return $this->idressource; }

    public function getNomressource(): ?string { return $this->nomressource; }
    public function setNomressource(string $nomressource): static { $this->nomressource = $nomressource; return $this; }

    public function getTyperessource(): ?string { return $this->typeressource; }
    public function setTyperessource(string $typeressource): static { $this->typeressource = $typeressource; return $this; }

    public function getQuantite(): ?int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getCout(): ?string { return $this->cout; }
    public function setCout(string $cout): static { $this->cout = $cout; return $this; }

    public function getFournisseur(): ?string { return $this->fournisseur; }
    public function setFournisseur(?string $fournisseur): static { $this->fournisseur = $fournisseur; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): static { $this->statut = $statut; return $this; }

    public function getDateajout(): ?\DateTimeInterface { return $this->dateajout; }
    public function setDateajout(\DateTimeInterface $dateajout): static { $this->dateajout = $dateajout; return $this; }

    public function getProject(): ?ProjectAgricole { return $this->project; }
    public function setProject(?ProjectAgricole $project): static { $this->project = $project; return $this; }

    public function __toString(): string
    {
        return $this->nomressource ?? '';
    }
}