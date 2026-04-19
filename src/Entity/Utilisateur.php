<?php
// src/Entity/Utilisateur.php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé')]
#[ORM\HasLifecycleCallbacks]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(max: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(max: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'Email invalide')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    private ?string $tel = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateInscrit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $derniereConnexion = null;

    #[ORM\Column(type: 'boolean')]
    private bool $estEnLigne = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    // ===== RECONNAISSANCE FACIALE =====

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $faceDescriptor = null;

    #[ORM\Column(type: 'boolean')]
    private bool $faceEnabled = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $faceEnrolledAt = null;

    // ===== RELATIONS =====

    #[ORM\OneToOne(mappedBy: 'utilisateur', targetEntity: Admin::class, cascade: ['persist', 'remove'])]
    private ?Admin $admin = null;

    #[ORM\OneToOne(mappedBy: 'utilisateur', targetEntity: Agriculteur::class, cascade: ['persist', 'remove'])]
    private ?Agriculteur $agriculteur = null;

    #[ORM\OneToOne(mappedBy: 'utilisateur', targetEntity: Banque::class, cascade: ['persist', 'remove'])]
    private ?Banque $banque = null;

    #[ORM\OneToOne(mappedBy: 'utilisateur', targetEntity: Parametres2fa::class, cascade: ['persist', 'remove'])]
    private ?Parametres2fa $parametres2fa = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Document::class, cascade: ['remove'])]
    private Collection $documents;

    #[ORM\OneToMany(mappedBy: 'expediteur', targetEntity: Message::class)]
    private Collection $messagesEnvoyes;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: TokenReinitialisation::class, cascade: ['remove'])]
    private Collection $tokensReinitialisation;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: FaceRecognitionLog::class, cascade: ['remove'])]
    private Collection $faceRecognitionLogs;

    private ?string $plainPassword = null;

    // ===== CONSTRUCTEUR =====

    public function __construct()
    {
        $this->documents              = new ArrayCollection();
        $this->messagesEnvoyes        = new ArrayCollection();
        $this->tokensReinitialisation = new ArrayCollection();
        $this->faceRecognitionLogs    = new ArrayCollection();
        $this->dateInscrit            = new \DateTime();
        $this->derniereConnexion      = new \DateTime();
        $this->isVerified             = false;
        $this->faceEnabled            = false;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->dateInscrit === null) {
            $this->dateInscrit = new \DateTime();
        }

        if ($this->derniereConnexion === null) {
            $this->derniereConnexion = new \DateTime();
        }
    }

    // ===== GETTERS / SETTERS =====

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;
        return $this;
    }

    public function getDateInscrit(): ?\DateTimeInterface
    {
        return $this->dateInscrit;
    }

    public function setDateInscrit(\DateTimeInterface $dateInscrit): self
    {
        $this->dateInscrit = $dateInscrit;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getDerniereConnexion(): ?\DateTimeInterface
    {
        return $this->derniereConnexion;
    }

    public function setDerniereConnexion(\DateTimeInterface $derniereConnexion): self
    {
        $this->derniereConnexion = $derniereConnexion;
        return $this;
    }

    public function isEstEnLigne(): bool
    {
        return $this->estEnLigne;
    }

    public function setEstEnLigne(bool $estEnLigne): self
    {
        $this->estEnLigne = $estEnLigne;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    // ===== GETTERS / SETTERS RECONNAISSANCE FACIALE =====

    public function getFaceDescriptor(): ?string
    {
        return $this->faceDescriptor;
    }

    public function setFaceDescriptor(?string $faceDescriptor): self
    {
        $this->faceDescriptor = $faceDescriptor;
        return $this;
    }

    public function isFaceEnabled(): bool
    {
        return $this->faceEnabled;
    }

    public function setFaceEnabled(bool $faceEnabled): self
    {
        $this->faceEnabled = $faceEnabled;
        return $this;
    }

    public function getFaceEnrolledAt(): ?\DateTimeInterface
    {
        return $this->faceEnrolledAt;
    }

    public function setFaceEnrolledAt(?\DateTimeInterface $faceEnrolledAt): self
    {
        $this->faceEnrolledAt = $faceEnrolledAt;
        return $this;
    }

    // ===== RELATIONS =====

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): self
    {
        if ($admin === null && $this->admin !== null) {
            $this->admin->setUtilisateur(null);
        }
        if ($admin !== null && $admin->getUtilisateur() !== $this) {
            $admin->setUtilisateur($this);
        }
        $this->admin = $admin;
        return $this;
    }

    public function getAgriculteur(): ?Agriculteur
    {
        return $this->agriculteur;
    }

    public function setAgriculteur(?Agriculteur $agriculteur): self
    {
        if ($agriculteur === null && $this->agriculteur !== null) {
            $this->agriculteur->setUtilisateur(null);
        }
        if ($agriculteur !== null && $agriculteur->getUtilisateur() !== $this) {
            $agriculteur->setUtilisateur($this);
        }
        $this->agriculteur = $agriculteur;
        return $this;
    }

    public function getBanque(): ?Banque
    {
        return $this->banque;
    }

    public function setBanque(?Banque $banque): self
    {
        if ($banque === null && $this->banque !== null) {
            $this->banque->setUtilisateur(null);
        }
        if ($banque !== null && $banque->getUtilisateur() !== $this) {
            $banque->setUtilisateur($this);
        }
        $this->banque = $banque;
        return $this;
    }

    public function getParametres2fa(): ?Parametres2fa
    {
        return $this->parametres2fa;
    }

    public function setParametres2fa(?Parametres2fa $parametres2fa): self
    {
        if ($parametres2fa === null && $this->parametres2fa !== null) {
            $this->parametres2fa->setUtilisateur(null);
        }
        if ($parametres2fa !== null && $parametres2fa->getUtilisateur() !== $this) {
            $parametres2fa->setUtilisateur($this);
        }
        $this->parametres2fa = $parametres2fa;
        return $this;
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setUtilisateur($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getUtilisateur() === $this) {
                $document->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getMessagesEnvoyes(): Collection
    {
        return $this->messagesEnvoyes;
    }

    public function getTokensReinitialisation(): Collection
    {
        return $this->tokensReinitialisation;
    }

    public function getFaceRecognitionLogs(): Collection
    {
        return $this->faceRecognitionLogs;
    }

    public function addFaceRecognitionLog(FaceRecognitionLog $log): self
    {
        if (!$this->faceRecognitionLogs->contains($log)) {
            $this->faceRecognitionLogs->add($log);
            $log->setUtilisateur($this);
        }
        return $this;
    }

    public function removeFaceRecognitionLog(FaceRecognitionLog $log): self
    {
        if ($this->faceRecognitionLogs->removeElement($log)) {
            if ($log->getUtilisateur() === $this) {
                $log->setUtilisateur(null);
            }
        }
        return $this;
    }

    // ===== MÉTHODES SYMFONY USER INTERFACE =====

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        if ($this->admin !== null) {
            $roles[] = 'ROLE_ADMIN';
        }

        if ($this->agriculteur !== null) {
            $roles[] = 'ROLE_AGRICULTEUR';
        }

        if ($this->banque !== null) {
            $roles[] = 'ROLE_BANQUE';
        }

        return array_values(array_unique($roles));
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    // ===== MÉTHODES UTILITAIRES =====

    public function getNomComplet(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }

    public function getTypeUtilisateur(): string
    {
        if ($this->admin !== null) {
            return 'Admin';
        }

        if ($this->agriculteur !== null) {
            return 'Agriculteur';
        }

        if ($this->banque !== null) {
            return 'Banque';
        }

        return 'Utilisateur';
    }

    public function has2FAEnabled(): bool
    {
        return $this->parametres2fa !== null
            && $this->parametres2fa->isEstActive();
    }

    public function getVerificationStatus(): string
    {
        if ($this->isVerified) {
            return 'Vérifié';
        }
        if ($this->hasFaceEnrolled()) {
            return 'Vérifié par reconnaissance faciale';
        }
        return 'En attente de vérification';
    }

    // ===== ✅ MÉTHODES LOGIN CORRIGÉES =====

    /**
     * ✅ FIX : Login classique
     * Un compte avec face enrollée est considéré vérifié
     */
    public function canLogin(): bool
    {
        return $this->isVerified || $this->hasFaceEnrolled();
    }

    /**
     * ✅ FIX PRINCIPAL : Login facial
     * Ne vérifie PAS isVerified - le visage = preuve d'identité
     */
    public function canLoginWithFace(): bool
    {
        return $this->faceEnabled
            && $this->faceDescriptor !== null;
    }

    // ===== MÉTHODES RECONNAISSANCE FACIALE =====

    /**
     * Vérifie si un visage est enregistré et actif
     */
    public function hasFaceEnrolled(): bool
    {
        return $this->faceDescriptor !== null
            && $this->faceEnabled;
    }

    /**
     * Statut d'enrollment pour l'affichage
     */
    public function getFaceEnrollmentStatus(): string
    {
        if (!$this->faceEnabled) {
            return 'Désactivé';
        }
        if ($this->faceDescriptor === null) {
            return 'Non configuré';
        }
        if ($this->faceEnrolledAt !== null) {
            return 'Actif depuis le ' . $this->faceEnrolledAt->format('d/m/Y à H:i');
        }
        return 'Actif';
    }
}