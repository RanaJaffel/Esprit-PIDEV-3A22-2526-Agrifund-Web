<?php

namespace App\Service;

use App\Entity\Agriculteur;

class AgriculteurManager
{
    public function validate(Agriculteur $agriculteur): bool
    {
        // ✅ Règle 1 : Superficie positive
        if ($agriculteur->getSuperficieferme() !== null &&
            $agriculteur->getSuperficieferme() <= 0) {
            throw new \InvalidArgumentException('La superficie doit être positive');
        }

        // ✅ Règle 2 : Type culture obligatoire
        if (empty($agriculteur->getTypeCulture())) {
            throw new \InvalidArgumentException('Le type de culture est obligatoire');
        }

        // ✅ Règle 3 : Status valide
        $statusValides = ['en_attente', 'accepte', 'refuse'];

        if (!in_array($agriculteur->getStatuscompte(), $statusValides)) {
            throw new \InvalidArgumentException('Statut invalide');
        }

        return true;
    }
}