<?php

namespace App\Entity;

use App\Repository\OffreFinanciereRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreFinanciereRepository::class)]
#[ORM\Table(name: 'offre_financiere')]
class OffreFinanciere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_offre')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_offre', length: 255)]
    private ?string $nomOffre = null;

    #[ORM\Column(name: 'conditions', type: 'text', nullable: true)]
    private ?string $conditions = null;

    #[ORM\Column(name: 'statut', length: 50)]
    private ?string $statut = null;

    #[ORM\ManyToOne(targetEntity: ProduitFinancier::class, inversedBy: 'offres')]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id_produit', nullable: false)]
    private ?ProduitFinancier $produitFinancier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomOffre(): ?string
    {
        return $this->nomOffre;
    }

    public function setNomOffre(string $nomOffre): static
    {
        $this->nomOffre = $nomOffre;
        return $this;
    }

    public function getConditions(): ?string
    {
        return $this->conditions;
    }

    public function setConditions(?string $conditions): static
    {
        $this->conditions = $conditions;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getProduitFinancier(): ?ProduitFinancier
    {
        return $this->produitFinancier;
    }

    public function setProduitFinancier(?ProduitFinancier $produitFinancier): static
    {
        $this->produitFinancier = $produitFinancier;
        return $this;
    }
}
