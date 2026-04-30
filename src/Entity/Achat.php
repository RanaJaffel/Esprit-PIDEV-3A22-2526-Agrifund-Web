<?php

declare(strict_types=1);

namespace App\Entity;

use App\Payment\AchatStatus;
use App\Repository\AchatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchatRepository::class)]
#[ORM\Table(name: 'achat')]
#[ORM\Index(name: 'idx_achat_reference', columns: ['reference'])]
#[ORM\Index(name: 'idx_achat_statut', columns: ['statut'])]
#[ORM\Index(name: 'idx_achat_user', columns: ['utilisateur_id'])]
#[ORM\HasLifecycleCallbacks]
class Achat
{
    public const TYPE_OFFER = 'offer';
    public const TYPE_PRODUCT = 'product';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'achat_id')]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $reference = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: OffreFinanciere::class)]
    #[ORM\JoinColumn(name: 'offre_id', nullable: true, onDelete: 'SET NULL')]
    private ?OffreFinanciere $offre = null;

    #[ORM\ManyToOne(targetEntity: ProduitFinancier::class)]
    #[ORM\JoinColumn(name: 'produit_id', nullable: true, onDelete: 'SET NULL')]
    private ?ProduitFinancier $produit = null;

    #[ORM\Column(name: 'type_cible', length: 20)]
    private string $typeCible = self::TYPE_OFFER;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $montant = '0.00';

    #[ORM\Column(name: 'montant_base', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $montantBase = '0.00';

    #[ORM\Column(name: 'montant_taxe', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $montantTaxe = '0.00';

    #[ORM\Column(name: 'invoice_email', length: 180, nullable: true)]
    private ?string $invoiceEmail = null;

    #[ORM\Column(length: 3)]
    private string $devise = 'TND';

    #[ORM\Column(length: 30)]
    private string $statut = AchatStatus::INITIATED;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'paid_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\OneToMany(
        mappedBy: 'achat',
        targetEntity: TransactionPaiement::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt ??= $now;
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
        if ($this->transactions->removeElement($transaction)) {
            if ($transaction->getAchat() === $this) {
                $transaction->setAchat(null);
            }
        }

        return $this;
    }
}
