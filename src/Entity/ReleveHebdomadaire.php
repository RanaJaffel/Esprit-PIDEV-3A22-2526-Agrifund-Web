<?php

namespace App\Entity;

use App\Repository\ReleveHebdomadaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReleveHebdomadaireRepository::class)]
#[ORM\Table(name: 'releve_hebdomadaire')]
class ReleveHebdomadaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_releve_hebdo')]
    private ?int $idReleveHebdo = null;

    #[ORM\Column(name: 'idproject')]
    private ?int $idproject = null;

    #[ORM\Column(name: 'date_debut', type: 'date')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'date_fin', type: 'date')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(name: 'temp_moyenne', nullable: true)]
    private ?float $tempMoyenne = 0;

    #[ORM\Column(name: 'humidite_moyenne', nullable: true)]
    private ?float $humiditeMoyenne = 0;

    #[ORM\Column(name: 'condition_dominante', length: 50, nullable: true)]
    private ?string $conditionDominante = 'NORMAL';

    #[ORM\Column(name: 'nb_mesures_total', nullable: true)]
    private ?int $nbMesuresTotal = 0;

    // ✅ IMPORTANT: forcer le type datetime (sinon Doctrine peut traiter comme string)
    #[ORM\Column(name: 'date_generation', type: 'datetime')]
    private ?\DateTimeInterface $dateGeneration = null;

    public function __construct()
    {
        $this->dateGeneration = new \DateTime(); // DateTime mutable
    }

    public function getIdReleveHebdo(): ?int
    {
        return $this->idReleveHebdo;
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

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
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

    public function setDateGeneration(\DateTimeInterface $dateGeneration): self
    {
        $this->dateGeneration = $dateGeneration;
        return $this;
    }
}