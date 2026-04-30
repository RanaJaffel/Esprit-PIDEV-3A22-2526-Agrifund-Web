<?php

namespace App\Service;

use App\Entity\ProjectAgricole;

/**
 * Service métier pour la validation des projets agricoles.
 *
 * Règles métier validées :
 *  1. Le nom du projet est obligatoire.
 *  2. La surface doit être strictement positive (> 0).
 *  3. Le budget demandé doit être strictement positif (> 0).
 *  4. Le statut doit être l'une des valeurs autorisées.
 *  5. La date de soumission ne peut pas être dans le futur.
 *  6. Si une coordonnée GPS est fournie, l'autre est obligatoire.
 */
class ProjectAgricoleManager
{
    private const STATUTS_AUTORISES = ['en cours', 'accepte', 'refuse'];

    /**
     * Valide toutes les règles métier d'un projet agricole.
     *
     * @throws \InvalidArgumentException si une règle est violée
     */
    public function validate(ProjectAgricole $project): bool
    {
        $this->validateNomProject($project);
        $this->validateSurface($project);
        $this->validateBudget($project);
        $this->validateStatut($project);
        $this->validateDateSoumission($project);
        $this->validateCoordonnees($project);

        return true;
    }

    // -------------------------------------------------------------------------
    // Règle 1 : Le nom du projet est obligatoire
    // -------------------------------------------------------------------------
    private function validateNomProject(ProjectAgricole $project): void
    {
        if (empty(trim((string) $project->getNomproject()))) {
            throw new \InvalidArgumentException('Le nom du projet est obligatoire.');
        }
    }

    // -------------------------------------------------------------------------
    // Règle 2 : La surface doit être strictement positive
    // -------------------------------------------------------------------------
    private function validateSurface(ProjectAgricole $project): void
    {
        $surface = $project->getSurface();

        if ($surface === null || $surface <= 0) {
            throw new \InvalidArgumentException('La surface doit être supérieure à zéro.');
        }
    }

    // -------------------------------------------------------------------------
    // Règle 3 : Le budget demandé doit être strictement positif
    // -------------------------------------------------------------------------
    private function validateBudget(ProjectAgricole $project): void
    {
        $budget = (float) $project->getBudgetdemande();

        if ($budget <= 0) {
            throw new \InvalidArgumentException('Le budget demandé doit être supérieur à zéro.');
        }
    }

    // -------------------------------------------------------------------------
    // Règle 4 : Le statut doit être l'une des valeurs autorisées
    // -------------------------------------------------------------------------
    private function validateStatut(ProjectAgricole $project): void
    {
        if (!in_array($project->getStatut(), self::STATUTS_AUTORISES, true)) {
            throw new \InvalidArgumentException(
                'Statut invalide. Valeurs autorisées : ' . implode(', ', self::STATUTS_AUTORISES) . '.'
            );
        }
    }

    // -------------------------------------------------------------------------
    // Règle 5 : La date de soumission ne peut pas être dans le futur
    // -------------------------------------------------------------------------
    private function validateDateSoumission(ProjectAgricole $project): void
    {
        $date = $project->getDatesoumission();

        if ($date === null) {
            throw new \InvalidArgumentException('La date de soumission est obligatoire.');
        }

        $today = new \DateTime('today');
        if ($date > $today) {
            throw new \InvalidArgumentException('La date de soumission ne peut pas être dans le futur.');
        }
    }

    // -------------------------------------------------------------------------
    // Règle 6 : Latitude et longitude doivent être fournies ensemble
    // -------------------------------------------------------------------------
    private function validateCoordonnees(ProjectAgricole $project): void
    {
        $lat = $project->getLatitude();
        $lon = $project->getLongitude();

        // L'une est fournie sans l'autre → invalide
        if (($lat === null) !== ($lon === null)) {
            throw new \InvalidArgumentException(
                'La latitude et la longitude doivent être fournies ensemble.'
            );
        }
    }
}
