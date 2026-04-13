<?php

namespace App\Entity;

use App\Repository\ProduitFinancierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitFinancierRepository::class)]
#[ORM\Table(name: 'produit_financier')]
#[ORM\Index(name: 'idx_produit_type', columns: ['type_financement'])]
class ProduitFinancier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_produit')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_produit', length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ0-9\s\-\'\.]+$/',
        message: 'Le nom du produit ne doit contenir que des lettres, chiffres, espaces et tirets.'
    )]
    private ?string $nomProduit = null;

    #[ORM\Column(name: 'type_financement', length: 100)]
    #[Assert\NotBlank(message: 'Le type de financement est obligatoire.')]
    #[Assert\Choice(
        choices: ['Crédit', 'Prêt', 'Leasing', 'Subvention', 'Microfinance'],
        message: 'Veuillez choisir un type de financement valide.'
    )]
    private ?string $typeFinancement = null;

    #[ORM\Column(name: 'taux_interet')]
    #[Assert\NotBlank(message: 'Le taux d\'intérêt est obligatoire.')]
    #[Assert\Type(type: 'numeric', message: 'Le taux doit être un nombre valide.')]
    #[Assert\Positive(message: 'Le taux d\'intérêt doit être positif.')]
    #[Assert\LessThanOrEqual(value: 100, message: 'Le taux d\'intérêt ne peut pas dépasser 100%.')]
    private ?float $tauxInteret = null;

    #[ORM\Column(name: 'montant_min')]
    #[Assert\NotBlank(message: 'Le montant minimum est obligatoire.')]
    #[Assert\Positive(message: 'Le montant minimum doit être supérieur à 0.')]
    private ?float $montantMin = null;

    #[ORM\Column(name: 'montant_max')]
    #[Assert\NotBlank(message: 'Le montant maximum est obligatoire.')]
    #[Assert\Positive(message: 'Le montant maximum doit être supérieur à 0.')]
    #[Assert\GreaterThan(propertyPath: 'montantMin', message: 'Le montant maximum doit être supérieur au montant minimum.')]
    private ?float $montantMax = null;

    #[ORM\Column(name: 'regles_financieres', type: 'text', nullable: true)]
    #[Assert\Length(
        max: 2000,
        maxMessage: 'Les règles financières ne peuvent pas dépasser {{ limit }} caractères.'
    )]
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

    public function __toString(): string
    {
        return $this->nomProduit ?? '';
    }
}
