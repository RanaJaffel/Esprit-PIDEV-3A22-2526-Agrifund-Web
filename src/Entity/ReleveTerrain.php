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
        message: 'Le type de mesure doit correspondre à un capteur valide'
    )]
    private ?string $typeMesure = null;

    #[ORM\Column(name: 'valeur_mesuree')]
    #[Assert\NotNull(message: 'La valeur mesurée est obligatoire')]
    #[Assert\Type(
        type: 'numeric',
        message: 'La valeur doit être un nombre valide'
    )]
    #[Assert\Range(
        min: -999999.99,
        max: 999999.99,
        notInRangeMessage: 'La valeur doit être comprise entre {{ min }} et {{ max }}'
    )]
    private ?float $valeurMesuree = null;

    #[ORM\Column(name: 'unite', length: 20)]
    #[Assert\NotBlank(message: 'L\'unité est obligatoire')]
    #[Assert\Length(
        max: 20,
        maxMessage: 'L\'unité ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[°%a-zA-Z0-9\/\s]+$/',
        message: 'L\'unité contient des caractères non valides'
    )]
    private ?string $unite = null;

    #[ORM\Column(name: 'date_heure')]
    #[Assert\NotNull(message: 'La date et heure sont obligatoires')]
    #[Assert\Type(\DateTimeImmutable::class, message: 'La date doit être une date valide')]
    #[Assert\LessThanOrEqual(
        '+1 minute',
        message: 'La date du relevé ne peut pas être dans le futur'
    )]
    private ?\DateTimeImmutable $dateHeure = null;

    #[ORM\Column(name: 'id_capteur')]
    #[Assert\NotNull(message: 'Le capteur est obligatoire')]
    #[Assert\Positive(message: 'L\'identifiant du capteur doit être un nombre positif')]
    private ?int $idCapteur = null;

    #[ORM\Column(name: 'idproject')]
    #[Assert\NotNull(message: 'Le projet est obligatoire')]
    #[Assert\Positive(message: 'L\'identifiant du projet doit être un nombre positif')]
    private ?int $idproject = null;

    #[ORM\Column(name: 'source_donnee', length: 20)]
    #[Assert\NotBlank(message: 'La source de données est obligatoire')]
    #[Assert\Choice(
        choices: ['MANUEL', 'AUTOMATIQUE', 'IMPORT', 'API'],
        message: 'La source doit être : MANUEL, AUTOMATIQUE, IMPORT ou API'
    )]
    private ?string $sourceDonnee = null;

    #[ORM\Column(name: 'qualite', length: 20)]
    #[Assert\NotBlank(message: 'La qualité est obligatoire')]
    #[Assert\Choice(
        choices: ['OK', 'SUSPECT', 'ERREUR_CAPTEUR', 'NON_VERIFIE'],
        message: 'La qualité doit être : OK, SUSPECT, ERREUR_CAPTEUR ou NON_VERIFIE'
    )]
    private ?string $qualite = null;

    #[ORM\Column(name: 'note', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'La note ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[\w\s\-.,;:!?()\'"À-ÿ]+$/u',
        message: 'La note contient des caractères non autorisés'
    )]
    private ?string $note = null;

    public function __construct()
    {
        $this->dateHeure = new \DateTimeImmutable();
        $this->sourceDonnee = 'MANUEL';
        $this->qualite = 'OK';
    }

    public function getIdReleve(): ?int
    {
        return $this->idReleve;
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

    public function getValeurMesuree(): ?float
    {
        return $this->valeurMesuree;
    }

    public function setValeurMesuree(?float $valeurMesuree): self
    {
        $this->valeurMesuree = $valeurMesuree;
        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(?string $unite): self
    {
        $this->unite = $unite;
        return $this;
    }

    public function getDateHeure(): ?\DateTimeImmutable
    {
        return $this->dateHeure;
    }

    public function setDateHeure(?\DateTimeImmutable $dateHeure): self
    {
        $this->dateHeure = $dateHeure;
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

    public function getSourceDonnee(): ?string
    {
        return $this->sourceDonnee;
    }

    public function setSourceDonnee(?string $sourceDonnee): self
    {
        $this->sourceDonnee = $sourceDonnee;
        return $this;
    }

    public function getQualite(): ?string
    {
        return $this->qualite;
    }

    public function setQualite(?string $qualite): self
    {
        $this->qualite = $qualite;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;
        return $this;
    }
}