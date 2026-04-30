<?php

declare(strict_types=1);

namespace App\Entity;

use App\Payment\PaymentProvider;
use App\Payment\PaymentStatus;
use App\Repository\TransactionPaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionPaiementRepository::class)]
#[ORM\Table(name: 'transaction_paiement')]
#[ORM\Index(name: 'idx_transaction_reference', columns: ['reference'])]
#[ORM\Index(name: 'idx_transaction_provider', columns: ['provider'])]
#[ORM\Index(name: 'idx_transaction_status', columns: ['statut'])]
#[ORM\HasLifecycleCallbacks]
class TransactionPaiement
{
    public const METHOD_CARD = 'card';
    public const METHOD_PAYPAL = 'paypal';
    public const METHOD_CRYPTO = 'crypto';
    public const METHOD_MANUAL = 'manual';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'transaction_id')]
    private ?int $id = null;

    /**
     * ✅ Correction suffix _id
     */
    #[ORM\ManyToOne(targetEntity: Achat::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(name: 'achat_id', referencedColumnName: 'achat_id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Achat $achat = null;

    #[ORM\Column(length: 64, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private ?string $reference = null;

    #[ORM\Column(length: 30)]
    #[Assert\Choice(callback: [PaymentProvider::class, 'all'])]
    private string $provider = PaymentProvider::MANUAL;

    #[ORM\Column(name: 'payment_method', length: 20)]
    #[Assert\Choice(choices: [self::METHOD_CARD, self::METHOD_PAYPAL, self::METHOD_CRYPTO, self::METHOD_MANUAL])]
    private string $paymentMethod = self::METHOD_CARD;

    #[ORM\Column(name: 'provider_payment_id', length: 190, nullable: true)]
    private ?string $providerPaymentId = null;

    #[ORM\Column(name: 'provider_event_id', length: 190, nullable: true, unique: true)]
    private ?string $providerEventId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\Positive]
    private string $montant = '0.00';

    #[ORM\Column(length: 3)]
    #[Assert\Length(min: 3, max: 3)]
    private string $devise = 'TND';

    #[ORM\Column(length: 30)]
    #[Assert\Choice(callback: [PaymentStatus::class, 'all'])]
    private string $statut = PaymentStatus::INITIATED;

    #[ORM\Column(name: 'verification_status', length: 30, options: ['default' => 'pending'])]
    private string $verificationStatus = 'pending';

    #[ORM\Column(name: 'verification_note', type: Types::TEXT, nullable: true)]
    private ?string $verificationNote = null;

    #[ORM\Column(name: 'verified_by', length: 180, nullable: true)]
    private ?string $verifiedBy = null;

    #[ORM\Column(name: 'verified_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $verifiedAt = null;

    #[ORM\Column(name: 'gateway_payload', type: Types::JSON, nullable: true)]
    private ?array $gatewayPayload = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'processed_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAchat(): ?Achat
    {
        return $this->achat;
    }

    public function setAchat(?Achat $achat): self
    {
        $this->achat = $achat;
        return $this;
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

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getProviderPaymentId(): ?string
    {
        return $this->providerPaymentId;
    }

    public function setProviderPaymentId(?string $providerPaymentId): self
    {
        $this->providerPaymentId = $providerPaymentId;
        return $this;
    }

    public function getProviderEventId(): ?string
    {
        return $this->providerEventId;
    }

    public function setProviderEventId(?string $providerEventId): self
    {
        $this->providerEventId = $providerEventId;
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

    public function getVerificationStatus(): string
    {
        return $this->verificationStatus;
    }

    public function setVerificationStatus(string $verificationStatus): self
    {
        $this->verificationStatus = $verificationStatus;
        return $this;
    }

    public function getVerificationNote(): ?string
    {
        return $this->verificationNote;
    }

    public function setVerificationNote(?string $verificationNote): self
    {
        $this->verificationNote = $verificationNote;
        return $this;
    }

    public function getVerifiedBy(): ?string
    {
        return $this->verifiedBy;
    }

    public function setVerifiedBy(?string $verifiedBy): self
    {
        $this->verifiedBy = $verifiedBy;
        return $this;
    }

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTimeImmutable $verifiedAt): self
    {
        $this->verifiedAt = $verifiedAt;
        return $this;
    }

    public function getGatewayPayload(): ?array
    {
        return $this->gatewayPayload;
    }

    public function setGatewayPayload(?array $gatewayPayload): self
    {
        $this->gatewayPayload = $gatewayPayload;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?\DateTimeImmutable $processedAt): self
    {
        $this->processedAt = $processedAt;
        return $this;
    }
}
