<?php

namespace App\Tests\Service;

use App\Entity\ProjectAgricole;
use App\Service\ProjetAgricoleManager;
use PHPUnit\Framework\TestCase;

class ProjetAgricoleManagerTest extends TestCase
{
    public function testValidProject(): void
    {
        $projet = new ProjectAgricole();
        $projet->setNomproject('Projet Blé 2026');
        $projet->setSurface(150.5);
        $projet->setBudgetdemande('50000.00');
        $projet->setStatut('en cours');

        $manager = new ProjetAgricoleManager();

        $this->assertTrue($manager->validate($projet));
    }

    public function testProjectWithoutName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du projet est obligatoire');

        $projet = new ProjectAgricole();
        $projet->setNomproject('');
        $projet->setSurface(100.0);
        $projet->setBudgetdemande('10000.00');
        $projet->setStatut('en cours');

        $manager = new ProjetAgricoleManager();
        $manager->validate($projet);
    }

    public function testProjectWithZeroSurface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La surface doit être supérieure à zéro');

        $projet = new ProjectAgricole();
        $projet->setNomproject('Projet Invalid');
        $projet->setSurface(0);
        $projet->setBudgetdemande('10000.00');
        $projet->setStatut('en cours');

        $manager = new ProjetAgricoleManager();
        $manager->validate($projet);
    }

    public function testProjectWithNegativeBudget(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le budget demandé doit être supérieur à zéro');

        $projet = new ProjectAgricole();
        $projet->setNomproject('Projet Invalid');
        $projet->setSurface(100.0);
        $projet->setBudgetdemande('-5000.00');
        $projet->setStatut('en cours');

        $manager = new ProjetAgricoleManager();
        $manager->validate($projet);
    }

    public function testProjectWithInvalidStatut(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut doit être "en cours", "accepte" ou "refuse"');

        $projet = new ProjectAgricole();
        $projet->setNomproject('Projet Test');
        $projet->setSurface(100.0);
        $projet->setBudgetdemande('10000.00');
        $projet->setStatut('annule');

        $manager = new ProjetAgricoleManager();
        $manager->validate($projet);
    }
}