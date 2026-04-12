<?php

namespace App\Entity;

use App\Repository\RapportJournalierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RapportJournalierRepository::class)]
#[ORM\Table(name: 'rapport_journalier')]
class RapportJournalier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_rapport')]
    private ?int $idRapport = null;

    #[ORM\Column(name: 'date_rapport', type: 'date')]
    #[Assert\NotNull(message: 'La date du rapport est obligatoire')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date doit être une date valide')]
    #[Assert\LessThanOrEqual(
        'today',
        message: 'La date du rapport ne peut pas être dans le futur'
    )]
    private ?\DateTimeInterface $dateRapport = null;

    #[ORM\Column(name: 'type_mesure', length: 50)]
    #[Assert\NotBlank(message: 'Le type de mesure est obligatoire')]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Le type de mesure ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Choice(
        choices: [
            'TEMPERATURE',
            'HUMIDITE_SOL',
            'PH_SOL',
            'PLUVIOMETRIE',
            'LUMINOSITE',
            'VENT',
            'HUMIDITE_AIR',
            'PRESSION'
        ],
        message: 'Le type de mesure doit correspondre à un type de capteur valide'
    )]
    private ?string $typeMesure = null;

    #[ORM\Column(name: 'moyenne')]
    #[Assert\NotNull(message: 'La moyenne est obligatoire')]
    #[Assert\Type(
        type: 'numeric',
        message: 'La moyenne doit être un nombre valide'
    )]
    #[Assert\Range(
        min: -999999.99,
        max: 999999.99,
        notInRangeMessage: 'La moyenne doit être comprise entre {{ min }} et {{ max }}'
    )]
    private ?float $moyenne = null;

    #[ORM\Column(name: 'min')]
    #[Assert\NotNull(message: 'Le minimum est obligatoire')]
    #[Assert\Type(
        type: 'numeric',
        message: 'Le minimum doit être un nombre valide'
    )]
    #[Assert\Range(
        min: -999999.99,
        max: 999999.99,
        notInRangeMessage: 'Le minimum doit être compris entre {{ min }} et {{ max }}'
    )]
    private ?float $min = null;

    #[ORM\Column(name: 'max')]
    #[Assert\NotNull(message: 'Le maximum est obligatoire')]
    #[Assert\Type(
        type: 'numeric',
        message: 'Le maximum doit être un nombre valide'
    )]
    #[Assert\Range(
        min: -999999.99,
        max: 999999.99,
        notInRangeMessage: 'Le maximum doit être compris entre {{ min }} et {{ max }}'
    )]
    #[Assert\GreaterThanOrEqual(
        propertyPath: 'min',
        message: 'Le maximum doit être supérieur ou égal au minimum'
    )]
    private ?float $max = null;

    #[ORM\Column(name: 'nb_mesures')]
    #[Assert\NotNull(message: 'Le nombre de mesures est obligatoire')]
    #[Assert\Type(
        type: 'integer',
        message: 'Le nombre de mesures doit être un entier'
    )]
    #[Assert\PositiveOrZero(
        message: 'Le nombre de mesures doit être positif ou zéro'
    )]
    #[Assert\Range(
        max: 10000,
        notInRangeMessage: 'Le nombre de mesures ne peut pas dépasser {{ max }}'
    )]
    private ?int $nbMesures = null;

    #[ORM\Column(name: 'condition_dominante', length: 50)]
    #[Assert\NotBlank(message: 'La condition dominante est obligatoire')]
    #[Assert\Choice(
        choices: [
            'NORMAL',
            'OPTIMAL',
            'CRITIQUE',
            'ALERTE',
            'DANGER',
            'INCONNU'
        ],
        message: 'La condition doit être : NORMAL, OPTIMAL, CRITIQUE, ALERTE, DANGER ou INCONNU'
    )]
    private ?string $conditionDominante = null;

    #[ORM\Column(name: 'id_capteur')]
    #[Assert\NotNull(message: 'Le capteur est obligatoire')]
    #[Assert\Positive(
        message: 'L\'identifiant du capteur doit être un nombre positif'
    )]
    private ?int $idCapteur = null;

    #[ORM\Column(name: 'idproject', nullable: true)]
    #[Assert\Positive(
        message: 'L\'identifiant du projet doit être un nombre positif'
    )]
    private ?int $idproject = null;

    #[Assert\Callback]
    public function validateMinMaxLessThanOrEqual(Assert\ExecutionContextInterface $context): void
    {
        if ($this->min !== null && $this->max !== null && $this->min > $this->max) {
            $context->buildViolation('Le minimum ne peut pas être supérieur au maximum')
                ->atPath('min')
                ->addViolation();
        }

        if ($this->moyenne !== null && $this->min !== null && $this->max !== null) {
            if ($this->moyenne < $this->min || $this->moyenne > $this->max) {
                $context->buildViolation('La moyenne doit être comprise entre le minimum et le maximum')
                    ->atPath('moyenne')
                    ->addViolation();
            }
        }
    }

    public function __construct()
    {
        $this->dateRapport = new \DateTimeImmutable();
        $this->nbMesures = 0;
        $this->conditionDominante = 'NORMAL';
    }

    public function getIdRapport(): ?int
    {
        return $this->idRapport;
    }

    public function getDateRapport(): ?\DateTimeInterface
    {
        return $this->dateRapport;
    }

    public function setDateRapport(?\DateTimeInterface $dateRapport): self
    {
        $this->dateRapport = $dateRapport;
        return $this;
    }

    public function getTypeMesure(): ?string
    {
        return $this->typeMesure;
    }

    public function setTypeMesure(?string $typeMesure): self
    {
        $this->typeMesure = $typeMesure;
        return $this;
    }

    public function getMoyenne(): ?float
    {
        return $this->moyenne;
    }

    public function setMoyenne(?float $moyenne): self
    {
        $this->moyenne = $moyenne;
        return $this;
    }

    public function getMin(): ?float
    {
        return $this->min;
    }

    public function setMin(?float $min): self
    {
        $this->min = $min;
        return $this;
    }

    public function getMax(): ?float
    {
        return $this->max;
    }

    public function setMax(?float $max): self
    {
        $this->max = $max;
        return $this;
    }

    public function getNbMesures(): ?int
    {
        return $this->nbMesures;
    }

    public function setNbMesures(?int $nbMesures): self
    {
        $this->nbMesures = $nbMesures;
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

    public function getIdCapteur(): ?int
    {
        return $this->idCapteur;
    }

    public function setIdCapteur(?int $idCapteur): self
    {
        $this->idCapteur = $idCapteur;
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
}