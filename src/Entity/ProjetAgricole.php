<?php

namespace App\Entity;

use App\Repository\ProjetAgricoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetAgricoleRepository::class)]
#[ORM\Table(name: 'projectagricole')]
class ProjetAgricole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idproject')]
    private ?int $idproject = null;

    #[ORM\Column(name: 'nomproject', length: 100)]
    private ?string $nomproject = null;

    #[ORM\Column(name: 'statut', type: 'string')]
    private ?string $statut = null;

    public function getIdproject(): ?int { return $this->idproject; }
    public function getNomproject(): ?string { return $this->nomproject; }
    public function setNomproject(string $v): self { $this->nomproject = $v; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $v): self { $this->statut = $v; return $this; }

    public function __toString(): string
    {
        return $this->nomproject ?? '';
    }
}