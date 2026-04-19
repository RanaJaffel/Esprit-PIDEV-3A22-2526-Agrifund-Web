<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
#[ORM\Index(columns: ['projet_id', 'created_at'], name: 'idx_notif_project_time')]
#[ORM\Index(columns: ['is_read', 'created_at'], name: 'idx_notif_unread_time')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'projet_id', type: Types::INTEGER)]
    private int $projetId;

    #[ORM\Column(name: 'capteur_id', type: Types::INTEGER, nullable: true)]
    private ?int $capteurId = null;

    #[ORM\Column(length: 40)]
    private string $type; // ANOMALIE_SEVERE, CAPTEUR_BLOQUE, CAPTEUR_MUET...

    #[ORM\Column(length: 10)]
    private string $severity; // INFO, WARN, CRITICAL

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column(name: 'is_read', type: Types::BOOLEAN)]
    private bool $isRead = false;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getProjetId(): int { return $this->projetId; }
    public function setProjetId(int $projetId): self { $this->projetId = $projetId; return $this; }

    public function getCapteurId(): ?int { return $this->capteurId; }
    public function setCapteurId(?int $capteurId): self { $this->capteurId = $capteurId; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }

    public function getSeverity(): string { return $this->severity; }
    public function setSeverity(string $severity): self { $this->severity = $severity; return $this; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): self { $this->message = $message; return $this; }

    public function isRead(): bool { return $this->isRead; }
    public function markRead(): self { $this->isRead = true; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}