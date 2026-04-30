<?php

namespace App\Service;

use App\Entity\Capteur;

class CapteurManager
{
    public function validate(Capteur $capteur): bool
    {
        // ✅ Règle 1 : Projet obligatoire (idproject)
        if (!$capteur->getIdproject()) {
            throw new \InvalidArgumentException("Le capteur doit être lié à un projet.");
        }

        // ✅ Règle 2 : Type valide (selon ton entité)
        $typesValides = [
            'TEMPERATURE',
            'HUMIDITE_SOL',
            'PH_SOL',
            'PLUVIOMETRIE',
            'LUMINOSITE',
            'VENT'
        ];

        if (!in_array($capteur->getTypeCapteur(), $typesValides)) {
            throw new \InvalidArgumentException("Type de capteur invalide.");
        }

        // ✅ Règle 3 : Date installation pas dans le futur
        if ($capteur->getDateInstallation() > new \DateTimeImmutable()) {
            throw new \InvalidArgumentException("La date d'installation ne peut pas être dans le futur.");
        }

        return true;
    }
}
