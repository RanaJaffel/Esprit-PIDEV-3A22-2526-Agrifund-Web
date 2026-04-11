<?php

namespace App\Entity;

use App\Repository\RapportJournalierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RapportJournalierRepository::class)]
#[ORM\Table(name: 'rapport_journalier')]
class RapportJournalier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_rapport')]
    private ?int $idRapport = null;

    #[ORM\Column(name: 'date_rapport', type: 'date')]
    private ?\DateTimeInterface $dateRapport = null;

    #[ORM\Column(name: 'type_mesure', length: 50)]
    private ?string $typeMesure = null;

    #[ORM\Column(name: 'moyenne')]
    private ?float $moyenne = null;

    #[ORM\Column(name: 'min')]
    private ?float $min = null;

    #[ORM\Column(name: 'max')]
    private ?float $max = null;

    #[ORM\Column(name: 'nb_mesures')]
    private int $nbMesures = 0;

    #[ORM\Column(name: 'condition_dominante', length: 50)]
    private string $conditionDominante = 'NORMAL';

    #[ORM\Column(name: 'id_capteur')]
    private ?int $idCapteur = null;

    #[ORM\Column(name: 'idproject', nullable: true)]
    private ?int $idproject = null;

    public function getIdRapport(): ?int
    {
        return $this->idRapport;
    }

    public function getDateRapport(): ?\DateTimeInterface
    {
        return $this->dateRapport;
    }

    public function setDateRapport(
        \DateTimeInterface $dateRapport
    ): self {
        $this->dateRapport = $dateRapport;
        return $this;
    }

    public function getTypeMesure(): ?string
    {
        return $this->typeMesure;
    }

    public function setTypeMesure(string $typeMesure): self
    {
        $this->typeMesure = $typeMesure;
        return $this;
    }

    public function getMoyenne(): ?float
    {
        return $this->moyenne;
    }

    public function setMoyenne(float $moyenne): self
    {
        $this->moyenne = $moyenne;
        return $this;
    }

    public function getMin(): ?float
    {
        return $this->min;
    }

    public function setMin(float $min): self
    {
        $this->min = $min;
        return $this;
    }

    public function getMax(): ?float
    {
        return $this->max;
    }

    public function setMax(float $max): self
    {
        $this->max = $max;
        return $this;
    }

    public function getNbMesures(): int
    {
        return $this->nbMesures;
    }

    public function setNbMesures(int $nbMesures): self
    {
        $this->nbMesures = $nbMesures;
        return $this;
    }

    public function getConditionDominante(): string
    {
        return $this->conditionDominante;
    }

    public function setConditionDominante(
        string $conditionDominante
    ): self {
        $this->conditionDominante = $conditionDominante;
        return $this;
    }

    public function getIdCapteur(): ?int
    {
        return $this->idCapteur;
    }

    public function setIdCapteur(int $idCapteur): self
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