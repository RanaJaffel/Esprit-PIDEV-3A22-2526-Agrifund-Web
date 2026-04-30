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
    #[Assert\Length(min: 3, max: 255)]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ0-9\s\-\'\.]+$/',
        message: 'Le nom du produit ne doit contenir que des lettres, chiffres, espaces et tirets.'
    )]
    private ?string $nomProduit = null;

    #[ORM\Column(name: 'type_financement', length: 100)]
    #[Assert\NotBlank]
    #[Assert\Choice(
        choices: ['Crédit', 'Prêt', 'Leasing', 'Subvention', 'Microfinance']
    )]
    private ?string $typeFinancement = null;

    #[ORM\Column(name: 'taux_interet')]
    #[Assert\NotBlank]
    #[Assert\Type('numeric')]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(100)]
    private ?float $tauxInteret = null;

    #[ORM\Column(name: 'montant')]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?float $montant = null;

    #[ORM\Column(
        name: 'prix_fixe',
        type: 'decimal',
        precision: 10,
        scale: 2,
        options: ['default' => 0]
    )]
    private string $prixFixe = '0.00';

    #[ORM\Column(name: 'regles_financieres', type: 'text', nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $reglesFinancieres = null;

    /**
     * ✅ CORRECTION IMPORTANTE ICI
     * - Suppression du cascade remove dangereux
     * - Ajout orphanRemoval
     */
    #[ORM\OneToMany(
        targetEntity: OffreFinanciere::class,
        mappedBy: 'produitFinancier',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
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

    public function setNomProduit(string $nomProduit): self
    {
        $this->nomProduit = $nomProduit;
        return $this;
    }

    public function getTypeFinancement(): ?string
    {
        return $this->typeFinancement;
    }

    public function setTypeFinancement(string $typeFinancement): self
    {
        $this->typeFinancement = $typeFinancement;
        return $this;
    }

    public function getTauxInteret(): ?float
    {
        return $this->tauxInteret;
    }

    public function setTauxInteret(float $tauxInteret): self
    {
        $this->tauxInteret = $tauxInteret;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getPrixFixe(): string
    {
        return $this->prixFixe;
    }

    public function setPrixFixe(string $prixFixe): self
    {
        $normalized = str_replace(',', '.', trim($prixFixe));

        if (preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized) === 1) {
            if (!str_contains($normalized, '.')) {
                $normalized .= '.00';
            } else {
                [$int, $dec] = explode('.', $normalized, 2);
                $normalized = $int . '.' . str_pad(substr($dec, 0, 2), 2, '0');
            }
        }

        $this->prixFixe = $normalized;
        return $this;
    }

    public function getReglesFinancieres(): ?string
    {
        return $this->reglesFinancieres;
    }

    public function setReglesFinancieres(?string $reglesFinancieres): self
    {
        $this->reglesFinancieres = $reglesFinancieres;
        return $this;
    }

    /**
     * @return Collection<int, OffreFinanciere>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(OffreFinanciere $offre): self
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setProduitFinancier($this);
        }

        return $this;
    }

    public function removeOffre(OffreFinanciere $offre): self
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
