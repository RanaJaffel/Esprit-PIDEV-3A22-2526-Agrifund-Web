<?php

namespace App\Entity;

use App\Repository\ReleveTerrainRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReleveTerrainRepository::class)]
#[ORM\Table(name: 'releve_terrain')]
class ReleveTerrain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_releve')]
    private ?int $idReleve = null;

    #[ORM\Column(name: 'type_mesure', length: 50)]
    #[Assert\NotBlank(message: 'Le type est obligatoire')]
    private ?string $typeMesure = null;

    #[ORM\Column(name: 'valeur_mesuree')]
    #[Assert\NotBlank(message: 'La valeur est obligatoire')]
    #[Assert\Type(type: 'float')]
    private ?float $valeurMesuree = null;

    #[ORM\Column(name: 'unite', length: 20)]
    #[Assert\NotBlank(message: 'L\'unité est obligatoire')]
    private ?string $unite = null;

    #[ORM\Column(name: 'date_heure')]
    private ?\DateTimeImmutable $dateHeure = null;

    #[ORM\Column(name: 'id_capteur')]
    #[Assert\NotNull(message: 'Le capteur est obligatoire')]
    private ?int $idCapteur = null;

    #[ORM\Column(name: 'idproject')]
    #[Assert\NotNull(message: 'Le projet est obligatoire')]
    private ?int $idproject = null;

    #[ORM\Column(name: 'source_donnee', length: 20)]
    private string $sourceDonnee = 'MANUEL';

    // ✅ AJOUT SURVEILLANCE
    #[ORM\Column(name: 'qualite', length: 20)]
    #[Assert\Choice(choices: ['OK','SUSPECT','ERREUR_CAPTEUR'], message: 'Qualité invalide')]
    private string $qualite = 'OK';

    #[ORM\Column(name: 'note', length: 255, nullable: true)]
    private ?string $note = null;

    public function __construct()
    {
        $this->dateHeure = new \DateTimeImmutable();
    }

    public function getIdReleve(): ?int { return $this->idReleve; }

    public function getTypeMesure(): ?string { return $this->typeMesure; }
    public function setTypeMesure(string $typeMesure): self { $this->typeMesure = $typeMesure; return $this; }

    public function getValeurMesuree(): ?float { return $this->valeurMesuree; }
    public function setValeurMesuree(float $valeurMesuree): self { $this->valeurMesuree = $valeurMesuree; return $this; }

    public function getUnite(): ?string { return $this->unite; }
    public function setUnite(string $unite): self { $this->unite = $unite; return $this; }

    public function getDateHeure(): ?\DateTimeImmutable { return $this->dateHeure; }
    public function setDateHeure(\DateTimeImmutable $dateHeure): self { $this->dateHeure = $dateHeure; return $this; }

    public function getIdCapteur(): ?int { return $this->idCapteur; }
    public function setIdCapteur(int $idCapteur): self { $this->idCapteur = $idCapteur; return $this; }

    public function getIdproject(): ?int { return $this->idproject; }
    public function setIdproject(int $idproject): self { $this->idproject = $idproject; return $this; }

    public function getSourceDonnee(): string { return $this->sourceDonnee; }
    public function setSourceDonnee(string $sourceDonnee): self { $this->sourceDonnee = $sourceDonnee; return $this; }

    public function getQualite(): string { return $this->qualite; }
    public function setQualite(string $qualite): self { $this->qualite = $qualite; return $this; }

    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): self { $this->note = $note; return $this; }
}