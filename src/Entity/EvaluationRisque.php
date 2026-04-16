<?php
namespace App\Entity;
use App\Repository\EvaluationRisqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationRisqueRepository::class)]
#[ORM\Table(name: 'evaluationrisque')]
class EvaluationRisque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idEvaluation')]
    private ?int $idEvaluation = null;

    #[ORM\Column(name: 'scoreGlobal')]
    private ?int $scoreGlobal = null;

    #[ORM\Column(name: 'niveauRisque', length: 20)]
    private ?string $niveauRisque = null;

    #[ORM\Column(name: 'fiabiliteDonnees', length: 255)]
    private ?string $fiabiliteDonnees = null;

    #[ORM\Column(name: 'facteurPrincipal', type: 'text')]
    private ?string $facteurPrincipal = null;

    // 🔥 CHANGÉ : int -> string (texte long pour "Je recommande...")
    #[ORM\Column(name: 'recommandation', type: 'text', nullable: true)]
    private ?string $recommandation = null;

    // 🔥 CHANGÉ : nullable pour éviter l'erreur si pas rempli
    #[ORM\Column(name: 'dateEvaluation', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateEvaluation = null;

    #[ORM\ManyToOne(targetEntity: ProjetAgricole::class)]
    #[ORM\JoinColumn(name: 'idProjet', referencedColumnName: 'idproject', nullable: false)]
    private ?ProjetAgricole $projet = null;

    #[ORM\Column(name: 'banqueId', nullable: true)]
    private ?int $banqueId = null;

    public function getIdEvaluation(): ?int { return $this->idEvaluation; }
    
    public function getScoreGlobal(): ?int { return $this->scoreGlobal; }
    public function setScoreGlobal(int $v): self { $this->scoreGlobal = $v; return $this; }
    
    public function getNiveauRisque(): ?string { return $this->niveauRisque; }
    public function setNiveauRisque(string $v): self { $this->niveauRisque = $v; return $this; }
    
    public function getFiabiliteDonnees(): ?string { return $this->fiabiliteDonnees; }
    public function setFiabiliteDonnees(string $v): self { $this->fiabiliteDonnees = $v; return $this; }
    
    public function getFacteurPrincipal(): ?string { return $this->facteurPrincipal; }
    public function setFacteurPrincipal(string $v): self { $this->facteurPrincipal = $v; return $this; }
    
    // 🔥 GETTER/SETTER mis à jour pour string
    public function getRecommandation(): ?string { return $this->recommandation; }
    public function setRecommandation(?string $v): self { $this->recommandation = $v; return $this; }
    
    // 🔥 GETTER/SETTER mis à jour pour nullable
    public function getDateEvaluation(): ?\DateTimeInterface { return $this->dateEvaluation; }
    public function setDateEvaluation(?\DateTimeInterface $v): self { $this->dateEvaluation = $v; return $this; }
    
    public function getProjet(): ?ProjetAgricole { return $this->projet; }
    public function setProjet(?ProjetAgricole $v): self { $this->projet = $v; return $this; }
    
    public function getBanqueId(): ?int { return $this->banqueId; }
    public function setBanqueId(?int $v): self { $this->banqueId = $v; return $this; }

    public function getNiveauRisqueNormalized(): string
    {
        $raw = mb_strtolower(trim($this->niveauRisque ?? ''));
        $map = ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ô' => 'o', 'û' => 'u'];
        $normalized = strtr($raw, $map);

        if (str_contains($normalized, 'faible'))  return 'faible';
        if (str_contains($normalized, 'moyen'))   return 'moyen';
        if (str_contains($normalized, 'eleve') || str_contains($normalized, 'elev')) return 'eleve';

        if ($this->scoreGlobal !== null) {
            if ($this->scoreGlobal >= 70) return 'faible';
            if ($this->scoreGlobal >= 40) return 'moyen';
            return 'eleve';
        }

        return 'eleve';
    }
}