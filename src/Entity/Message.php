<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'message')]
#[ORM\HasLifecycleCallbacks]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(name: 'conversation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'messagesEnvoyes')]
    #[ORM\JoinColumn(name: 'expediteur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $expediteur = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le message ne peut pas être vide')]
    private ?string $contenu = null;

    #[ORM\Column(type: 'boolean')]
    private bool $aPieceJointe = false;

    #[ORM\Column(type: 'integer')]
    private int $nbPiecesJointes = 0;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateEnvoi = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(type: 'boolean')]
    private bool $estLu = false;

    #[ORM\Column(type: 'boolean')]
    private bool $estSupprime = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateLecture = null;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: PieceJointe::class, cascade: ['persist', 'remove'])]
    private Collection $piecesJointes;

    public function __construct()
    {
        $this->piecesJointes = new ArrayCollection();
        $this->dateEnvoi = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setDateEnvoiValue(): void
    {
        if ($this->dateEnvoi === null) {
            $this->dateEnvoi = new \DateTime();
        }
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function getExpediteur(): ?Utilisateur
    {
        return $this->expediteur;
    }

    public function setExpediteur(?Utilisateur $expediteur): self
    {
        $this->expediteur = $expediteur;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function isAPieceJointe(): bool
    {
        return $this->aPieceJointe;
    }

    public function setAPieceJointe(bool $aPieceJointe): self
    {
        $this->aPieceJointe = $aPieceJointe;
        return $this;
    }

    public function getNbPiecesJointes(): int
    {
        return $this->nbPiecesJointes;
    }

    public function setNbPiecesJointes(int $nbPiecesJointes): self
    {
        $this->nbPiecesJointes = $nbPiecesJointes;
        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $dateEnvoi): self
    {
        $this->dateEnvoi = $dateEnvoi;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): self
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function isEstLu(): bool
    {
        return $this->estLu;
    }

    public function setEstLu(bool $estLu): self
    {
        $this->estLu = $estLu;
        if ($estLu && $this->dateLecture === null) {
            $this->dateLecture = new \DateTime();
        }
        return $this;
    }

    public function isEstSupprime(): bool
    {
        return $this->estSupprime;
    }

    public function setEstSupprime(bool $estSupprime): self
    {
        $this->estSupprime = $estSupprime;
        return $this;
    }

    public function getDateLecture(): ?\DateTimeInterface
    {
        return $this->dateLecture;
    }

    public function setDateLecture(?\DateTimeInterface $dateLecture): self
    {
        $this->dateLecture = $dateLecture;
        return $this;
    }

    /**
     * @return Collection<int, PieceJointe>
     */
    public function getPiecesJointes(): Collection
    {
        return $this->piecesJointes;
    }

    public function addPieceJointe(PieceJointe $pieceJointe): self
    {
        if (!$this->piecesJointes->contains($pieceJointe)) {
            $this->piecesJointes->add($pieceJointe);
            $pieceJointe->setMessage($this);
            $this->setAPieceJointe(true);
            $this->setNbPiecesJointes($this->piecesJointes->count());
        }
        return $this;
    }

    public function removePieceJointe(PieceJointe $pieceJointe): self
    {
        if ($this->piecesJointes->removeElement($pieceJointe)) {
            if ($pieceJointe->getMessage() === $this) {
                $pieceJointe->setMessage(null);
            }
            $this->setNbPiecesJointes($this->piecesJointes->count());
            if ($this->nbPiecesJointes === 0) {
                $this->setAPieceJointe(false);
            }
        }
        return $this;
    }

    public function isModifie(): bool
    {
        return $this->dateModification !== null;
    }
}