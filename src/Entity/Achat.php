<?php

declare(strict_types=1);

namespace App\Entity;

use App\Payment\AchatStatus;
use App\Repository\AchatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AchatRepository::class)]
#[ORM\Table(name: 'achat')]
#[ORM\Index(name: 'idx_achat_reference', columns: ['reference'])]
#[ORM\Index(name: 'idx_achat_statut', columns: ['statut'])]
#[ORM\Index(name: 'idx_achat_user', columns: ['id_utilisateur'])]
#[ORM\HasLifecycleCallbacks]
class Achat
{
    public const TYPE_OFFER = 'offer';
    public const TYPE_PRODUCT = 'product';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_achat')]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private ?string $reference = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: OffreFinanciere::class)]
    #[ORM\JoinColumn(name: 'id_offre', referencedColumnName: 'id_offre', nullable: true, onDelete: 'SET NULL')]
    private ?OffreFinanciere $offre = null;

    #[ORM\ManyToOne(targetEntity: ProduitFinancier::class)]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id_produit', nullable: true, onDelete: 'SET NULL')]
    private ?ProduitFinancier $produit = null;

    #[ORM\Column(name: 'type_cible', length: 20)]
    #[Assert\Choice(choices: [self::TYPE_OFFER, self::TYPE_PRODUCT])]
    private string $typeCible = self::TYPE_OFFER;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\Positive]
    private string $montant = '0.00';

    #[ORM\Column(name: 'montant_base', type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\PositiveOrZero]
    private string $montantBase = '0.00';

    #[ORM\Column(name: 'montant_taxe', type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\PositiveOrZero]
    private string $montantTaxe = '0.00';

    #[ORM\Column(name: 'invoice_email', length: 180, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $invoiceEmail = null;

    #[ORM\Column(length: 3)]
    #[Assert\Length(min: 3, max: 3)]
    private string $devise = 'TND';

    #[ORM\Column(length: 30)]
    #[Assert\Choice(callback: [AchatStatus::class, 'all'])]
    private string $statut = AchatStatus::INITIATED;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'paid_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\OneToMany(mappedBy: 'achat', targetEntity: TransactionPaiement::class, cascade: ['persist', 'remove'])]
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        if ($this->createdAt === null) {
            $this->createdAt = $now;
        }

        if ($this->updatedAt === null) {
            $this->updatedAt = $now;
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
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

    public function getOffre(): ?OffreFinanciere
    {
        return $this->offre;
    }

    public function setOffre(?OffreFinanciere $offre): self
    {
        $this->offre = $offre;

        return $this;
    }

    public function getProduit(): ?ProduitFinancier
    {
        return $this->produit;
    }

    public function setProduit(?ProduitFinancier $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getTypeCible(): string
    {
        return $this->typeCible;
    }

    public function setTypeCible(string $typeCible): self
    {
        $this->typeCible = $typeCible;

        return $this;
    }

    public function getMontant(): string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getMontantBase(): string
    {
        return $this->montantBase;
    }

    public function setMontantBase(string $montantBase): self
    {
        $this->montantBase = $montantBase;

        return $this;
    }

    public function getMontantTaxe(): string
    {
        return $this->montantTaxe;
    }

    public function setMontantTaxe(string $montantTaxe): self
    {
        $this->montantTaxe = $montantTaxe;

        return $this;
    }

    public function getInvoiceEmail(): ?string
    {
        return $this->invoiceEmail;
    }

    public function setInvoiceEmail(?string $invoiceEmail): self
    {
        $this->invoiceEmail = $invoiceEmail;

        return $this;
    }

    public function getDevise(): string
    {
        return $this->devise;
    }

    public function setDevise(string $devise): self
    {
        $this->devise = strtoupper($devise);

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): self
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    /**
     * @return Collection<int, TransactionPaiement>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(TransactionPaiement $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setAchat($this);
        }

        return $this;
    }

    public function removeTransaction(TransactionPaiement $transaction): self
    {
        if ($this->transactions->removeElement($transaction) && $transaction->getAchat() === $this) {
            $transaction->setAchat(null);
        }

        return $this;
    }
}
