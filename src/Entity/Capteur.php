<?php

namespace App\Entity;

use App\Repository\CapteurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CapteurRepository::class)]
#[ORM\Table(name: 'capteur')]
class Capteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_capteur')]
    private ?int $idCapteur = null;

    #[ORM\Column(name: 'typeCapteur', length: 50)]
    #[Assert\NotBlank(message: 'Le type est obligatoire')]
    #[Assert\Choice(choices: [
        'TEMPERATURE', 'HUMIDITE_SOL', 
        'PH_SOL', 'PLUVIOMETRIE', 
        'LUMINOSITE', 'VENT'
    ])]
    private ?string $typeCapteur = null;

    #[ORM\Column(name: 'modele', length: 100)]
    private string $modele = 'Simulateur Python';

    #[ORM\Column(name: 'localisation', length: 100)]
    #[Assert\NotBlank(message: 'La localisation est obligatoire')]
    private ?string $localisation = null;

    #[ORM\Column(name: 'statut', length: 20)]
    private string $statut = 'ACTIF';

    #[ORM\Column(name: 'date_installation')]
    private ?\DateTimeImmutable $dateInstallation = null;

    #[ORM\Column(name: 'idproject')]
    #[Assert\NotNull(message: 'Le projet est obligatoire')]
    private ?int $idproject = null;

    #[ORM\Column(name: 'id_user')]
    private ?int $idUser = null;

    public function __construct()
    {
        $this->dateInstallation = new \DateTimeImmutable();
    }

    public function getIdCapteur(): ?int
    {
        return $this->idCapteur;
    }

    public function getTypeCapteur(): ?string
    {
        return $this->typeCapteur;
    }

    public function setTypeCapteur(string $typeCapteur): self
    {
        $this->typeCapteur = $typeCapteur;
        return $this;
    }

    public function getModele(): string
    {
        return $this->modele;
    }

    public function setModele(string $modele): self
    {
        $this->modele = $modele;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateInstallation(): ?\DateTimeImmutable
    {
        return $this->dateInstallation;
    }

    public function setDateInstallation(
        \DateTimeImmutable $dateInstallation
    ): self {
        $this->dateInstallation = $dateInstallation;
        return $this;
    }

    public function getIdproject(): ?int
    {
        return $this->idproject;
    }

    public function setIdproject(int $idproject): self
    {
        $this->idproject = $idproject;
        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(int $idUser): self
    {
        $this->idUser = $idUser;
        return $this;
    }
}