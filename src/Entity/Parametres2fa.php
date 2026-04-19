<?php
// src/Entity/Parametres2fa.php

namespace App\Entity;

use App\Repository\Parametres2faRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Parametres2faRepository::class)]
#[ORM\Table(name: 'parametres2fa')]
class Parametres2fa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'parametres2fa', targetEntity: Utilisateur::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: 'boolean')]
    private bool $estActive = false;

    #[ORM\Column(type: 'string', length: 20)]
    private string $methodePreferee = 'email';

    #[ORM\Column(name: 'telephone_2fa',type: 'string', length: 20, nullable: true)]
    private ?string $telephone2fa = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateActivation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function isEstActive(): bool
    {
        return $this->estActive;
    }

    public function setEstActive(bool $estActive): self
    {
        $this->estActive = $estActive;
        return $this;
    }

    public function getMethodePreferee(): string
    {
        return $this->methodePreferee;
    }

    public function setMethodePreferee(string $methodePreferee): self
    {
        $this->methodePreferee = $methodePreferee;
        return $this;
    }

    public function getTelephone2fa(): ?string
    {
        return $this->telephone2fa;
    }

    public function setTelephone2fa(?string $telephone2fa): self
    {
        $this->telephone2fa = $telephone2fa;
        return $this;
    }

    public function getDateActivation(): ?\DateTimeInterface
    {
        return $this->dateActivation;
    }

    public function setDateActivation(?\DateTimeInterface $dateActivation): self
    {
        $this->dateActivation = $dateActivation;
        return $this;
    }
}