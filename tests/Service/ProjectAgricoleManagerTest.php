<?php

namespace App\Tests\Service;

use App\Entity\ProjectAgricole;
use App\Service\ProjectAgricoleManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour ProjectAgricoleManager.
 *
 * Règles métier couvertes :
 *  1. Le nom du projet est obligatoire.
 *  2. La surface doit être strictement positive (> 0).
 *  3. Le budget demandé doit être strictement positif (> 0).
 *  4. Le statut doit être l'une des valeurs autorisées.
 *  5. La date de soumission ne peut pas être dans le futur.
 *  6. Latitude et longitude doivent être fournies ensemble.
 */
class ProjectAgricoleManagerTest extends TestCase
{
    private ProjectAgricoleManager $manager;

    // =========================================================================
    // Setup
    // =========================================================================

    protected function setUp(): void
    {
        $this->manager = new ProjectAgricoleManager();
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Retourne un projet valide servant de base pour chaque test.
     * On modifie seulement le champ testé dans chaque méthode de test.
     */
    private function buildValidProject(): ProjectAgricole
    {
        $project = new ProjectAgricole();
        $project->setNomproject('Projet Blé Bio');
        $project->setSurface(5.0);
        $project->setBudgetdemande('2500.00');
        $project->setStatut('en cours');
        $project->setDatesoumission(new \DateTime('today'));

        return $project;
    }

    // =========================================================================
    // Règle 1 – Nom du projet obligatoire
    // =========================================================================

    /** Un projet avec toutes les données valides doit passer la validation. */
    public function testValidProjectPassesValidation(): void
    {
        $project = $this->buildValidProject();

        $this->assertTrue($this->manager->validate($project));
    }

    /** Un nom vide doit lever une InvalidArgumentException. */
    public function testProjectWithEmptyNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du projet est obligatoire.');

        $project = $this->buildValidProject();
        $project->setNomproject('');

        $this->manager->validate($project);
    }

    /** Un nom composé uniquement d'espaces doit également être rejeté. */
    public function testProjectWithWhitespaceOnlyNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du projet est obligatoire.');

        $project = $this->buildValidProject();
        $project->setNomproject('   ');

        $this->manager->validate($project);
    }

    // =========================================================================
    // Règle 2 – Surface strictement positive
    // =========================================================================

    /** Une surface nulle doit lever une InvalidArgumentException. */
    public function testProjectWithZeroSurfaceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La surface doit être supérieure à zéro.');

        $project = $this->buildValidProject();
        $project->setSurface(0);

        $this->manager->validate($project);
    }

    /** Une surface négative doit lever une InvalidArgumentException. */
    public function testProjectWithNegativeSurfaceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La surface doit être supérieure à zéro.');

        $project = $this->buildValidProject();
        $project->setSurface(-10.5);

        $this->manager->validate($project);
    }

    // =========================================================================
    // Règle 3 – Budget strictement positif
    // =========================================================================

    /** Un budget nul doit lever une InvalidArgumentException. */
    public function testProjectWithZeroBudgetThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le budget demandé doit être supérieur à zéro.');

        $project = $this->buildValidProject();
        $project->setBudgetdemande('0');

        $this->manager->validate($project);
    }

    /** Un budget négatif doit lever une InvalidArgumentException. */
    public function testProjectWithNegativeBudgetThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le budget demandé doit être supérieur à zéro.');

        $project = $this->buildValidProject();
        $project->setBudgetdemande('-500.00');

        $this->manager->validate($project);
    }

    // =========================================================================
    // Règle 4 – Statut dans la liste autorisée
    // =========================================================================

    /** Un statut inconnu doit lever une InvalidArgumentException. */
    public function testProjectWithInvalidStatutThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Statut invalide.');

        $project = $this->buildValidProject();
        $project->setStatut('inconnu');

        $this->manager->validate($project);
    }

    /** Chaque statut autorisé doit être accepté. */
    public function testProjectWithAllowedStatutsPassesValidation(): void
    {
        foreach (['en cours', 'accepte', 'refuse'] as $statut) {
            $project = $this->buildValidProject();
            $project->setStatut($statut);

            $this->assertTrue(
                $this->manager->validate($project),
                "Le statut \"$statut\" devrait être accepté."
            );
        }
    }

    // =========================================================================
    // Règle 5 – Date de soumission non future
    // =========================================================================

    /** Une date dans le futur doit lever une InvalidArgumentException. */
    public function testProjectWithFutureDateThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de soumission ne peut pas être dans le futur.');

        $project = $this->buildValidProject();
        $project->setDatesoumission(new \DateTime('+1 day'));

        $this->manager->validate($project);
    }

    /** La date d'aujourd'hui doit être acceptée. */
    public function testProjectWithTodayDatePassesValidation(): void
    {
        $project = $this->buildValidProject();
        $project->setDatesoumission(new \DateTime('today'));

        $this->assertTrue($this->manager->validate($project));
    }

    /** Une date passée doit être acceptée. */
    public function testProjectWithPastDatePassesValidation(): void
    {
        $project = $this->buildValidProject();
        $project->setDatesoumission(new \DateTime('-30 days'));

        $this->assertTrue($this->manager->validate($project));
    }

    // =========================================================================
    // Règle 6 – Latitude et longitude fournies ensemble
    // =========================================================================

    /** Fournir latitude sans longitude doit lever une InvalidArgumentException. */
    public function testProjectWithLatitudeOnlyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La latitude et la longitude doivent être fournies ensemble.');

        $project = $this->buildValidProject();
        $project->setLatitude(36.8065);
        // longitude reste null

        $this->manager->validate($project);
    }

    /** Fournir longitude sans latitude doit lever une InvalidArgumentException. */
    public function testProjectWithLongitudeOnlyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La latitude et la longitude doivent être fournies ensemble.');

        $project = $this->buildValidProject();
        $project->setLongitude(10.1815);
        // latitude reste null

        $this->manager->validate($project);
    }

    /** Fournir les deux coordonnées doit être accepté. */
    public function testProjectWithBothCoordinatesPassesValidation(): void
    {
        $project = $this->buildValidProject();
        $project->setLatitude(36.8065);
        $project->setLongitude(10.1815);

        $this->assertTrue($this->manager->validate($project));
    }

    /** Aucune coordonnée (les deux null) doit être accepté. */
    public function testProjectWithNoCoordinatesPassesValidation(): void
    {
        $project = $this->buildValidProject();
        // latitude et longitude restent null par défaut

        $this->assertTrue($this->manager->validate($project));
    }
}
