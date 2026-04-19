<?php

namespace App\Entity;

use App\Repository\BanqueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: BanqueRepository::class)]
#[ORM\Table(name: 'banque')]
#[UniqueEntity(fields: ['codebanque'], message: 'Ce code banque est déjà utilisé')]
class Banque
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'banque', targetEntity: Utilisateur::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le code banque est obligatoire')]
    #[Assert\Length(max: 50)]
    private ?string $codebanque = null;

    #[ORM\Column(name: 'addresseSiege', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $addresseSiege = null;

    #[ORM\Column(name: 'representantLegal', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $representantLegal = null;

    #[ORM\Column(name: 'adresseAgence', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $adresseAgence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'URL invalide')]
    private ?string $siteweb = null;

    #[ORM\Column(name: 'statusCompte', length: 50)]
    private string $statusCompte = 'en_attente';

    #[ORM\Column(name: 'compteVerfiee', type: 'boolean')]
    private bool $compteVerfiee = false;

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

    public function getCodebanque(): ?string
    {
        return $this->codebanque;
    }

    public function setCodebanque(string $codebanque): self
    {
        $this->codebanque = $codebanque;

        return $this;
    }

    public function getAddresseSiege(): ?string
    {
        return $this->addresseSiege;
    }

    public function setAddresseSiege(?string $addresseSiege): self
    {
        $this->addresseSiege = $addresseSiege;

        return $this;
    }

    public function getRepresentantLegal(): ?string
    {
        return $this->representantLegal;
    }

    public function setRepresentantLegal(?string $representantLegal): self
    {
        $this->representantLegal = $representantLegal;

        return $this;
    }

    public function getAdresseAgence(): ?string
    {
        return $this->adresseAgence;
    }

    public function setAdresseAgence(?string $adresseAgence): self
    {
        $this->adresseAgence = $adresseAgence;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getSiteweb(): ?string
    {
        return $this->siteweb;
    }

    public function setSiteweb(?string $siteweb): self
    {
        $this->siteweb = $siteweb;

        return $this;
    }

    public function getStatusCompte(): string
    {
        return $this->statusCompte;
    }

    public function setStatusCompte(string $statusCompte): self
    {
        $this->statusCompte = $statusCompte;

        return $this;
    }

    public function isCompteVerfiee(): bool
    {
        return $this->compteVerfiee;
    }

    public function setCompteVerfiee(bool $compteVerfiee): self
    {
        $this->compteVerfiee = $compteVerfiee;

        return $this;
    }
}