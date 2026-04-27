<?php

namespace App\Entity;

use App\Repository\OffreFinanciereRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OffreFinanciereRepository::class)]
#[ORM\Table(name: 'offre_financiere')]
#[ORM\Index(name: 'idx_offre_statut', columns: ['statut'])]
class OffreFinanciere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_offre')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_offre', length: 255)]
    #[Assert\NotBlank(message: 'Le nom de l\'offre est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ0-9\s\-\'\.]+$/',
        message: 'Le nom ne doit contenir que des lettres, chiffres, espaces et tirets.'
    )]
    private ?string $nomOffre = null;

    #[ORM\Column(name: 'conditions', type: 'text', nullable: true)]
    #[Assert\Length(
        max: 2000,
        maxMessage: 'Les conditions ne peuvent pas dépasser {{ limit }} caractères.'
    )]
    private ?string $conditions = null;

    #[ORM\Column(name: 'statut', length: 50)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    #[Assert\Choice(
        choices: ['Active', 'En attente', 'Cancelled'],
        message: 'Veuillez choisir un statut valide.'
    )]
    private ?string $statut = null;

    #[ORM\ManyToOne(targetEntity: ProduitFinancier::class, inversedBy: 'offres')]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id_produit', nullable: false)]
    #[Assert\NotNull(message: 'Le produit financier associé est obligatoire.')]
    private ?ProduitFinancier $produitFinancier = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => 0])]
    private string $prix = '0.00';

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

    public function getPrix(): string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;
        return $this;
    }
}
