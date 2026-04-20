<?php

namespace App\Entity;

use App\Repository\CapteurRepository;
use Doctrine\DBAL\Types\Types;
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
    #[ORM\Column(type: 'float', nullable: true)]
private ?float $latitude = null;

#[ORM\Column(type: 'float', nullable: true)]
private ?float $longitude = null;

    #[ORM\Column(name: 'typeCapteur', length: 50)]
    #[Assert\NotBlank(message: 'Le type de capteur est obligatoire')]
    #[Assert\Choice(
        choices: ['TEMPERATURE','HUMIDITE_SOL','PH_SOL','PLUVIOMETRIE','LUMINOSITE','VENT'],
        message: 'Le type doit être l\'un des suivants : TEMPERATURE, HUMIDITE_SOL, PH_SOL, PLUVIOMETRIE, LUMINOSITE, VENT'
    )]
    private ?string $typeCapteur = null;

    #[ORM\Column(name: 'modele', length: 100)]
    #[Assert\NotBlank(message: 'Le modèle est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le modèle doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le modèle ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s\-_]+$/',
        message: 'Le modèle ne peut contenir que des lettres, chiffres, espaces, tirets et underscores'
    )]
    private ?string $modele = null;

    #[ORM\Column(name: 'localisation', length: 100)]
    #[Assert\NotBlank(message: 'La localisation est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'La localisation doit contenir au moins {{ limit }} caractères',
        maxMessage: 'La localisation ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $localisation = null;

    #[ORM\Column(name: 'statut', length: 20)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(
        choices: ['ACTIF', 'INACTIF', 'MAINTENANCE', 'ERREUR'],
        message: 'Le statut doit être l\'un des suivants : ACTIF, INACTIF, MAINTENANCE, ERREUR'
    )]
    private ?string $statut = null;

    #[ORM\Column(name: 'date_installation', type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull(message: 'La date d\'installation est obligatoire')]
    #[Assert\Type(\DateTimeImmutable::class, message: 'La date doit être une date valide')]
    #[Assert\LessThanOrEqual(
        'now',
        message: 'La date d\'installation ne peut pas être dans le futur'
    )]
    private ?\DateTimeImmutable $dateInstallation = null;

    #[ORM\Column(name: 'idproject')]
    #[Assert\NotNull(message: 'Le projet est obligatoire')]
    #[Assert\Positive(message: 'L\'identifiant du projet doit être un nombre positif')]
    private ?int $idproject = null;

    #[ORM\Column(name: 'id_user', nullable: true)]
    #[Assert\PositiveOrZero(message: 'L\'identifiant utilisateur doit être un nombre positif ou zéro')]
    private ?int $idUser = null;

    #[ORM\Column(name: 'last_seen_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastSeenAt = null;

    public function __construct()
    {
        $this->dateInstallation = new \DateTimeImmutable(); // maintenant (autorisé car <= now)
        $this->statut = 'ACTIF';
        $this->modele = 'Simulateur Python';
    }

    public function getIdCapteur(): ?int
    {
        return $this->idCapteur;
    }

    public function getTypeCapteur(): ?string
    {
        return $this->typeCapteur;
    }

    public function setTypeCapteur(?string $typeCapteur): self
    {
        $this->typeCapteur = $typeCapteur;
        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(?string $modele): self
    {
        $this->modele = $modele;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): self
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateInstallation(): ?\DateTimeImmutable
    {
        return $this->dateInstallation;
    }

    public function setDateInstallation(?\DateTimeImmutable $dateInstallation): self
    {
        $this->dateInstallation = $dateInstallation;
        return $this;
    }

    public function getIdproject(): ?int
    {
        return $this->idproject;
    }

    public function setIdproject(?int $idproject): self
    {
        $this->idproject = $idproject;
        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(?int $idUser): self
    {
        $this->idUser = $idUser;
        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeInterface
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(?\DateTimeInterface $dt): self
    {
        $this->lastSeenAt = $dt;
        return $this;
    }
    public function getLatitude(): ?float
{
    return $this->latitude;
}

public function setLatitude(?float $latitude): self
{
    $this->latitude = $latitude;
    return $this;
}

public function getLongitude(): ?float
{
    return $this->longitude;
}

public function setLongitude(?float $longitude): self
{
    $this->longitude = $longitude;
    return $this;
}
}