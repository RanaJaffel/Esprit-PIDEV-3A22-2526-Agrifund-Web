<?php

namespace App\Entity;

use App\Repository\BlockchainRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlockchainRecordRepository::class)]
#[ORM\Table(name: 'blockchain_records')]
#[ORM\HasLifecycleCallbacks]
class BlockchainRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Hash du bloc actuel (SHA-256)
     */
    #[ORM\Column(length: 64)]
    private string $dataHash;

    /**
     * Hash du bloc précédent
     */
    #[ORM\Column(length: 64)]
    private string $previousHash;

    /**
     * Données stockées dans le bloc (JSON)
     * Exemple:
     * {
     *   "type": "IRRIGATION_DECISION",
     *   "projetId": 1,
     *   "decision": "IRRIGUER",
     *   "timestamp": 1715000000,
     *   "nonce": 42
     * }
     */
    #[ORM\Column(type: 'json')]
    private array $data = [];

    /**
     * Date de création en base
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /**
     * Optionnel : type de bloc (DATA, IRRIGATION, REPORT, etc.)
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $blockType = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // ================================
    // GETTERS & SETTERS
    // ================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDataHash(): string
    {
        return $this->dataHash;
    }

    public function setDataHash(string $dataHash): self
    {
        $this->dataHash = $dataHash;
        return $this;
    }

    public function getPreviousHash(): string
    {
        return $this->previousHash;
    }

    public function setPreviousHash(string $previousHash): self
    {
        $this->previousHash = $previousHash;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getBlockType(): ?string
    {
        return $this->blockType;
    }

    public function setBlockType(?string $blockType): self
    {
        $this->blockType = $blockType;
        return $this;
    }

    // ================================
    // MÉTHODES MÉTIER
    // ================================

    /**
     * Retourne une version courte du hash
     */
    public function getShortHash(int $length = 16): string
    {
        return substr($this->dataHash, 0, $length);
    }

    /**
     * Retourne un résumé lisible des données
     */
    public function getSummary(): string
    {
        if (isset($this->data['type'])) {
            return $this->data['type'];
        }

        return 'BLOCK #' . $this->id;
    }

    /**
     * Vérifie si bloc genesis
     */
    public function isGenesis(): bool
    {
        return $this->previousHash === str_repeat('0', 64);
    }
}