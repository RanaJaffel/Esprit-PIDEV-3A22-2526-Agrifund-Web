<?php

namespace App\Service;

use App\Entity\ReleveHebdomadaire;

class ReleveHebdomadaireManager
{
    public function validate(ReleveHebdomadaire $releve): bool
    {
        // ✅ Règle 1 : Date fin > date début
        if ($releve->getDateFin() <= $releve->getDateDebut()) {
            throw new \InvalidArgumentException(
                'La date de fin doit être postérieure à la date de début'
            );
        }

        // ✅ Règle 2 : Température valide
        if ($releve->getTempMoyenne() < -50 || $releve->getTempMoyenne() > 60) {
            throw new \InvalidArgumentException(
                'La température moyenne est invalide'
            );
        }

        // ✅ Règle 3 : Humidité valide
        if ($releve->getHumiditeMoyenne() < 0 || $releve->getHumiditeMoyenne() > 100) {
            throw new \InvalidArgumentException(
                'L’humidité moyenne est invalide'
            );
        }

        return true;
    }
}