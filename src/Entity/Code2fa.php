<?php
// src/Entity/Code2fa.php

namespace App\Entity;

use App\Repository\Code2faRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Code2faRepository::class)]
#[ORM\Table(name: 'code2fa')]
class Code2fa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: 'string', length: 6)]
    private ?string $code = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\Column(type: 'boolean')]
    private bool $estUtilise = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateUtilisation = null;

    #[ORM\Column(type: 'string', length: 10)]
    private string $typeEnvoi = 'email';

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->dateExpiration = new \DateTime('+5 minutes');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
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

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }

    public function isEstUtilise(): bool
    {
        return $this->estUtilise;
    }

    public function setEstUtilise(bool $estUtilise): self
    {
        $this->estUtilise = $estUtilise;
        return $this;
    }

    public function getDateUtilisation(): ?\DateTimeInterface
    {
        return $this->dateUtilisation;
    }

    public function setDateUtilisation(?\DateTimeInterface $dateUtilisation): self
    {
        $this->dateUtilisation = $dateUtilisation;
        return $this;
    }

    public function getTypeEnvoi(): string
    {
        return $this->typeEnvoi;
    }

    public function setTypeEnvoi(string $typeEnvoi): self
    {
        $this->typeEnvoi = $typeEnvoi;
        return $this;
    }

    public function isExpire(): bool
    {
        return new \DateTime() > $this->dateExpiration;
    }

    public function isValide(): bool
    {
        return !$this->estUtilise && !$this->isExpire();
    }
}