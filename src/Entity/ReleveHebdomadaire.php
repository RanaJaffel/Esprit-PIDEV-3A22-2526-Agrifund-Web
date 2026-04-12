<?php

namespace App\Entity;

use App\Repository\ReleveHebdomadaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReleveHebdomadaireRepository::class)]
#[ORM\Table(name: 'releve_hebdomadaire')]
class ReleveHebdomadaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_releve_hebdo')]
    private ?int $idReleveHebdo = null;

    #[ORM\Column(name: 'idproject')]
    #[Assert\NotNull(message: 'Le projet est obligatoire')]
    #[Assert\Positive(message: 'L\'identifiant du projet doit être un nombre positif')]
    private ?int $idproject = null;

    #[ORM\Column(name: 'date_debut', type: 'date')]
    #[Assert\NotNull(message: 'La date de début est obligatoire')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date de début doit être une date valide')]
    #[Assert\LessThanOrEqual(
        propertyPath: 'dateFin',
        message: 'La date de début doit être antérieure ou égale à la date de fin'
    )]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'date_fin', type: 'date')]
    #[Assert\NotNull(message: 'La date de fin est obligatoire')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date de fin doit être une date valide')]
    #[Assert\GreaterThanOrEqual(
        propertyPath: 'dateDebut',
        message: 'La date de fin doit être postérieure ou égale à la date de début'
    )]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(name: 'temp_moyenne', nullable: true)]
    #[Assert\Type(
        type: 'numeric',
        message: 'La température moyenne doit être un nombre valide'
    )]
    #[Assert\Range(
        min: -50.0,
        max: 60.0,
        notInRangeMessage: 'La température doit être comprise entre {{ min }}°C et {{ max }}°C'
    )]
    private ?float $tempMoyenne = null;

    #[ORM\Column(name: 'humidite_moyenne', nullable: true)]
    #[Assert\Type(
        type: 'numeric',
        message: 'L\'humidité moyenne doit être un nombre valide'
    )]
    #[Assert\Range(
        min: 0.0,
        max: 100.0,
        notInRangeMessage: 'L\'humidité doit être comprise entre {{ min }}% et {{ max }}%'
    )]
    private ?float $humiditeMoyenne = null;

    #[ORM\Column(name: 'condition_dominante', length: 50, nullable: true)]
    #[Assert\Length(
        max: 50,
        maxMessage: 'La condition dominante ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Choice(
        choices: [
            'NORMAL',
            'OPTIMAL',
            'CRITIQUE',
            'ALERTE',
            'DANGER',
            'INCONNU',
            'SECHERESSE',
            'INONDATION'
        ],
        message: 'La condition doit être : NORMAL, OPTIMAL, CRITIQUE, ALERTE, DANGER, INCONNU, SECHERESSE ou INONDATION'
    )]
    private ?string $conditionDominante = null;

    #[ORM\Column(name: 'nb_mesures_total', nullable: true)]
    #[Assert\Type(
        type: 'integer',
        message: 'Le nombre de mesures doit être un entier'
    )]
    #[Assert\PositiveOrZero(
        message: 'Le nombre de mesures doit être positif ou zéro'
    )]
    #[Assert\Range(
        max: 100000,
        notInRangeMessage: 'Le nombre de mesures ne peut pas dépasser {{ max }}'
    )]
    private ?int $nbMesuresTotal = null;

    #[ORM\Column(name: 'date_generation', type: 'datetime')]
    #[Assert\NotNull(message: 'La date de génération est obligatoire')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date de génération doit être une date valide')]
    #[Assert\LessThanOrEqual(
        '+1 minute',
        message: 'La date de génération ne peut pas être dans le futur'
    )]
    private ?\DateTimeInterface $dateGeneration = null;

    #[Assert\Callback]
    public function validatePeriode(Assert\ExecutionContextInterface $context): void
    {
        if ($this->dateDebut !== null && $this->dateFin !== null) {
            $interval = $this->dateDebut->diff($this->dateFin);
            $jours = $interval->days;

            // Vérifier que la période fait bien 7 jours (hebdomadaire)
            if ($jours !== 6) { // 6 jours de différence = 7 jours de période
                $context->buildViolation('La période doit couvrir exactement une semaine (7 jours)')
                    ->atPath('dateFin')
                    ->addViolation();
            }

            // Vérifier que la date de génération est postérieure à la fin de période
            if ($this->dateGeneration !== null && $this->dateGeneration < $this->dateFin) {
                $context->buildViolation('La date de génération doit être postérieure à la date de fin de période')
                    ->atPath('dateGeneration')
                    ->addViolation();
            }
        }
    }

    public function __construct()
    {
        $this->dateGeneration = new \DateTime();
        $this->tempMoyenne = 0;
        $this->humiditeMoyenne = 0;
        $this->nbMesuresTotal = 0;
        $this->conditionDominante = 'NORMAL';
    }

    public function getIdReleveHebdo(): ?int
    {
        return $this->idReleveHebdo;
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

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getTempMoyenne(): ?float
    {
        return $this->tempMoyenne;
    }

    public function setTempMoyenne(?float $tempMoyenne): self
    {
        $this->tempMoyenne = $tempMoyenne;
        return $this;
    }

    public function getHumiditeMoyenne(): ?float
    {
        return $this->humiditeMoyenne;
    }

    public function setHumiditeMoyenne(?float $humiditeMoyenne): self
    {
        $this->humiditeMoyenne = $humiditeMoyenne;
        return $this;
    }

    public function getConditionDominante(): ?string
    {
        return $this->conditionDominante;
    }

    public function setConditionDominante(?string $conditionDominante): self
    {
        $this->conditionDominante = $conditionDominante;
        return $this;
    }

    public function getNbMesuresTotal(): ?int
    {
        return $this->nbMesuresTotal;
    }

    public function setNbMesuresTotal(?int $nbMesuresTotal): self
    {
        $this->nbMesuresTotal = $nbMesuresTotal;
        return $this;
    }

    public function getDateGeneration(): ?\DateTimeInterface
    {
        return $this->dateGeneration;
    }

    public function setDateGeneration(?\DateTimeInterface $dateGeneration): self
    {
        $this->dateGeneration = $dateGeneration;
        return $this;
    }
}