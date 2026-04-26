<?php

namespace App\Service;

use App\Entity\ProjectAgricole;

class ProjetAgricoleManager
{
    public function validate(ProjectAgricole $projet): bool
    {
        // Rule 1: Project name is mandatory
        if (empty($projet->getNomproject())) {
            throw new \InvalidArgumentException('Le nom du projet est obligatoire');
        }

        // Rule 2: Surface must be strictly positive
        if ($projet->getSurface() === null || $projet->getSurface() <= 0) {
            throw new \InvalidArgumentException('La surface doit être supérieure à zéro');
        }

        // Rule 3: Budget must be strictly positive
        $budget = (float) $projet->getBudgetdemande();
        if ($projet->getBudgetdemande() === null || $budget <= 0) {
            throw new \InvalidArgumentException('Le budget demandé doit être supérieur à zéro');
        }

        // Rule 4: Status must be a valid enum value
        $allowedStatuts = ['en cours', 'accepte', 'refuse'];
        if (!in_array($projet->getStatut(), $allowedStatuts, true)) {
            throw new \InvalidArgumentException('Le statut doit être "en cours", "accepte" ou "refuse"');
        }

        return true;
    }
}