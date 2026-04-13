<?php

namespace App\Entity;

use App\Repository\ProjectAgricoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectAgricoleRepository::class)]
#[ORM\Table(name: 'projectagricole')]
class ProjectAgricole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idproject')]
    private ?int $idproject = null;

    #[ORM\Column(name: 'nomproject', length: 100)]
    private ?string $nomproject = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $surface = null;

    #[ORM\Column(name: 'budgetdemande', type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $budgetdemande = null;

    #[ORM\Column(columnDefinition: "ENUM('en cours','accepte','refuse') NOT NULL DEFAULT 'en cours'")]
    private ?string $statut = null;

    #[ORM\Column(name: 'datesoumission', type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datesoumission = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $longitude = null;

    #[ORM\ManyToOne(targetEntity: Agriculteur::class)]
    #[ORM\JoinColumn(name: 'agriculteur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Agriculteur $agriculteur = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: RessourceProject::class, cascade: ['remove'])]
    private Collection $ressources;

    public function __construct()
    {
        $this->ressources = new ArrayCollection();
        $this->statut = 'en cours';
        $this->datesoumission = new \DateTime();
    }

    public function getIdproject(): ?int { return $this->idproject; }

    public function getNomproject(): ?string { return $this->nomproject; }
    public function setNomproject(string $nomproject): static { $this->nomproject = $nomproject; return $this; }

    public function getSurface(): ?float { return $this->surface; }
    public function setSurface(float $surface): static { $this->surface = $surface; return $this; }

    public function getBudgetdemande(): ?string { return $this->budgetdemande; }
    public function setBudgetdemande(string $budgetdemande): static { $this->budgetdemande = $budgetdemande; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getDatesoumission(): ?\DateTimeInterface { return $this->datesoumission; }
    public function setDatesoumission(\DateTimeInterface $datesoumission): static { $this->datesoumission = $datesoumission; return $this; }

    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): static { $this->latitude = $latitude; return $this; }

    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): static { $this->longitude = $longitude; return $this; }

    public function getAgriculteur(): ?Agriculteur { return $this->agriculteur; }
    public function setAgriculteur(?Agriculteur $agriculteur): static { $this->agriculteur = $agriculteur; return $this; }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function getRessources(): Collection { return $this->ressources; }

    public function __toString(): string
    {
        return $this->nomproject ?? '';
    }
}