<?php

namespace App\Entity;

use App\Repository\ProduitFinancierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitFinancierRepository::class)]
#[ORM\Table(name: 'produit_financier')]
class ProduitFinancier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_produit')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_produit', length: 255)]
    private ?string $nomProduit = null;

    #[ORM\Column(name: 'type_financement', length: 100)]
    private ?string $typeFinancement = null;

    #[ORM\Column(name: 'taux_interet')]
    private ?float $tauxInteret = null;

    #[ORM\Column(name: 'montant_min')]
    private ?float $montantMin = null;

    #[ORM\Column(name: 'montant_max')]
    private ?float $montantMax = null;

    #[ORM\Column(name: 'regles_financieres', type: 'text', nullable: true)]
    private ?string $reglesFinancieres = null;

    #[ORM\OneToMany(targetEntity: OffreFinanciere::class, mappedBy: 'produitFinancier', cascade: ['remove'])]
    private Collection $offres;

    public function __construct()
    {
        $this->offres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomProduit(): ?string
    {
        return $this->nomProduit;
    }

    public function setNomProduit(string $nomProduit): static
    {
        $this->nomProduit = $nomProduit;
        return $this;
    }

    public function getTypeFinancement(): ?string
    {
        return $this->typeFinancement;
    }

    public function setTypeFinancement(string $typeFinancement): static
    {
        $this->typeFinancement = $typeFinancement;
        return $this;
    }

    public function getTauxInteret(): ?float
    {
        return $this->tauxInteret;
    }

    public function setTauxInteret(float $tauxInteret): static
    {
        $this->tauxInteret = $tauxInteret;
        return $this;
    }

    public function getMontantMin(): ?float
    {
        return $this->montantMin;
    }

    public function setMontantMin(float $montantMin): static
    {
        $this->montantMin = $montantMin;
        return $this;
    }

    public function getMontantMax(): ?float
    {
        return $this->montantMax;
    }

    public function setMontantMax(float $montantMax): static
    {
        $this->montantMax = $montantMax;
        return $this;
    }

    public function getReglesFinancieres(): ?string
    {
        return $this->reglesFinancieres;
    }

    public function setReglesFinancieres(?string $reglesFinancieres): static
    {
        $this->reglesFinancieres = $reglesFinancieres;
        return $this;
    }

    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(OffreFinanciere $offre): static
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setProduitFinancier($this);
        }
        return $this;
    }

    public function removeOffre(OffreFinanciere $offre): static
    {
        if ($this->offres->removeElement($offre)) {
            if ($offre->getProduitFinancier() === $this) {
                $offre->setProduitFinancier(null);
            }
        }
        return $this;
    }
}
