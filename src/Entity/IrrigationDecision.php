<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'irrigation_decisions')]
class IrrigationDecision
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $projetId;

    #[ORM\Column(length: 50)]
    private string $decision;

    #[ORM\Column(length: 50)]
    private string $quantite;

    #[ORM\Column(type: 'float')]
    private float $confidence;

    #[ORM\Column(type: 'json')]
    private array $inputData = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getProjetId(): int { return $this->projetId; }
    public function setProjetId(int $projetId): self { $this->projetId = $projetId; return $this; }

    public function getDecision(): string { return $this->decision; }
    public function setDecision(string $decision): self { $this->decision = $decision; return $this; }

    public function getQuantite(): string { return $this->quantite; }
    public function setQuantite(string $quantite): self { $this->quantite = $quantite; return $this; }

    public function getConfidence(): float { return $this->confidence; }
    public function setConfidence(float $confidence): self { $this->confidence = $confidence; return $this; }

    public function getInputData(): array { return $this->inputData; }
    public function setInputData(array $inputData): self { $this->inputData = $inputData; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}