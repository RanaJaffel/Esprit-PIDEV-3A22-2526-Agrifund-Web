<?php

namespace App\Entity;

use App\Repository\DecisionFinanciereRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DecisionFinanciereRepository::class)]
#[ORM\Table(name: 'decisionfinanciere')]
class DecisionFinanciere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idDecision')]
    private ?int $idDecision = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(choices: ['approuve', 'refuse', 'en_attente'])]
    private ?string $statut = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La justification est obligatoire')]
    private ?string $justification = null;

    #[ORM\Column(name: 'dateDecision', type: 'datetime')]
    private ?\DateTimeInterface $dateDecision = null;

    #[ORM\ManyToOne(targetEntity: EvaluationRisque::class)]
    #[ORM\JoinColumn(name: 'idEvaluation', referencedColumnName: 'idEvaluation', nullable: false)]
    private ?EvaluationRisque $evaluation = null;

    #[ORM\Column(name: 'banqueId', nullable: true)]
    private ?int $banqueId = null;

    public function __construct()
    {
        $this->dateDecision = new \DateTime();
    }

    public function getIdDecision(): ?int { return $this->idDecision; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $v): self { $this->statut = $v; return $this; }
    public function getJustification(): ?string { return $this->justification; }
    public function setJustification(string $v): self { $this->justification = $v; return $this; }
    public function getDateDecision(): ?\DateTimeInterface { return $this->dateDecision; }
    public function setDateDecision(\DateTimeInterface $v): self { $this->dateDecision = $v; return $this; }
    public function getEvaluation(): ?EvaluationRisque { return $this->evaluation; }
    public function setEvaluation(?EvaluationRisque $v): self { $this->evaluation = $v; return $this; }
    public function getBanqueId(): ?int { return $this->banqueId; }
    public function setBanqueId(?int $v): self { $this->banqueId = $v; return $this; }
}