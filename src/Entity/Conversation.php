<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\Table(name: 'conversation')]
#[ORM\UniqueConstraint(name: 'unique_conversation', columns: ['utilisateur_min', 'utilisateur_max'])]
#[ORM\HasLifecycleCallbacks]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'utilisateur1_id')]
    private ?int $utilisateur1Id = null;

    #[ORM\Column(name: 'utilisateur2_id')]
    private ?int $utilisateur2Id = null;

    #[ORM\Column(name: 'utilisateur_min', insertable: false, updatable: false)]
    private ?int $utilisateurMin = null;

    #[ORM\Column(name: 'utilisateur_max', insertable: false, updatable: false)]
    private ?int $utilisateurMax = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $derniereActivite = null;

    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: Message::class, cascade: ['remove'])]
    #[ORM\OrderBy(['dateEnvoi' => 'ASC'])]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->derniereActivite = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setCreatedValue(): void
    {
        if ($this->dateCreation === null) {
            $this->dateCreation = new \DateTime();
        }
        if ($this->derniereActivite === null) {
            $this->derniereActivite = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur1Id(): ?int
    {
        return $this->utilisateur1Id;
    }

    public function setUtilisateur1Id(int $utilisateur1Id): self
    {
        $this->utilisateur1Id = $utilisateur1Id;
        return $this;
    }

    public function getUtilisateur2Id(): ?int
    {
        return $this->utilisateur2Id;
    }

    public function setUtilisateur2Id(int $utilisateur2Id): self
    {
        $this->utilisateur2Id = $utilisateur2Id;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDerniereActivite(): ?\DateTimeInterface
    {
        return $this->derniereActivite;
    }

    public function setDerniereActivite(\DateTimeInterface $derniereActivite): self
    {
        $this->derniereActivite = $derniereActivite;
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }
        return $this;
    }

    public function getOtherUserId(int $currentUserId): ?int
    {
        if ($this->utilisateur1Id === $currentUserId) {
            return $this->utilisateur2Id;
        }
        return $this->utilisateur1Id;
    }

    public function getLastMessage(): ?Message
    {
        $messages = $this->messages->toArray();
        if (empty($messages)) {
            return null;
        }
        
        usort($messages, function($a, $b) {
            return $b->getDateEnvoi() <=> $a->getDateEnvoi();
        });
        
        return $messages[0];
    }

    public function hasUnreadMessages(int $userId): bool
    {
        foreach ($this->messages as $message) {
            if ($message->getExpediteur()->getId() !== $userId && !$message->isEstLu()) {
                return true;
            }
        }
        return false;
    }

    public function getUnreadMessagesCount(int $userId): int
    {
        $count = 0;
        foreach ($this->messages as $message) {
            if ($message->getExpediteur()->getId() !== $userId && !$message->isEstLu()) {
                $count++;
            }
        }
        return $count;
    }
}