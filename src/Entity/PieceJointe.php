<?php

namespace App\Entity;

use App\Repository\PieceJointeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PieceJointeRepository::class)]
#[ORM\Table(name: 'piecejointe')]
#[ORM\HasLifecycleCallbacks]
class PieceJointe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Message::class, inversedBy: 'piecesJointes')]
    #[ORM\JoinColumn(name: 'message_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Message $message = null;

    #[ORM\Column(length: 20)]
    private ?string $typeFichier = null;

    #[ORM\Column(length: 255)]
    private ?string $nomOriginal = null;

    #[ORM\Column(length: 255)]
    private ?string $nomStockage = null;

    #[ORM\Column(length: 500)]
    private ?string $cheminFichier = null;

    #[ORM\Column(type: 'bigint')]
    private ?int $tailleOctets = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $extension = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateUpload = null;

    public function __construct()
    {
        $this->dateUpload = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setDateUploadValue(): void
    {
        if ($this->dateUpload === null) {
            $this->dateUpload = new \DateTime();
        }
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getTypeFichier(): ?string
    {
        return $this->typeFichier;
    }

    public function setTypeFichier(string $typeFichier): self
    {
        $this->typeFichier = $typeFichier;
        return $this;
    }

    public function getNomOriginal(): ?string
    {
        return $this->nomOriginal;
    }

    public function setNomOriginal(string $nomOriginal): self
    {
        $this->nomOriginal = $nomOriginal;
        return $this;
    }

    public function getNomStockage(): ?string
    {
        return $this->nomStockage;
    }

    public function setNomStockage(string $nomStockage): self
    {
        $this->nomStockage = $nomStockage;
        return $this;
    }

    public function getCheminFichier(): ?string
    {
        return $this->cheminFichier;
    }

    public function setCheminFichier(string $cheminFichier): self
    {
        $this->cheminFichier = $cheminFichier;
        return $this;
    }

    public function getTailleOctets(): ?int
    {
        return $this->tailleOctets;
    }

    public function setTailleOctets(int $tailleOctets): self
    {
        $this->tailleOctets = $tailleOctets;
        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getDateUpload(): ?\DateTimeInterface
    {
        return $this->dateUpload;
    }

    public function setDateUpload(\DateTimeInterface $dateUpload): self
    {
        $this->dateUpload = $dateUpload;
        return $this;
    }

    public function getTailleFormatee(): string
    {
        $bytes = $this->tailleOctets;
        $units = ['o', 'Ko', 'Mo', 'Go'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}