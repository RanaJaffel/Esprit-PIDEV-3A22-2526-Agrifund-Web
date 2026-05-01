<?php

namespace App\Tests\Service;

use App\Entity\Agriculteur;
use App\Service\AgriculteurManager;
use PHPUnit\Framework\TestCase;

class AgriculteurManagerTest extends TestCase
{
    public function testValidAgriculteur()
    {
        $agriculteur = new Agriculteur();
        $agriculteur->setSuperficieferme(10);
        $agriculteur->setTypeCulture('Blé');
        $agriculteur->setStatuscompte('en_attente');

        $manager = new AgriculteurManager();

        $this->assertTrue($manager->validate($agriculteur));
    }

    public function testSuperficieNegative()
    {
        $this->expectException(\InvalidArgumentException::class);

        $agriculteur = new Agriculteur();
        $agriculteur->setSuperficieferme(-5);
        $agriculteur->setTypeCulture('Maïs');
        $agriculteur->setStatuscompte('en_attente');

        $manager = new AgriculteurManager();
        $manager->validate($agriculteur);
    }

    public function testTypeCultureVide()
    {
        $this->expectException(\InvalidArgumentException::class);

        $agriculteur = new Agriculteur();
        $agriculteur->setSuperficieferme(20);
        $agriculteur->setTypeCulture('');
        $agriculteur->setStatuscompte('en_attente');

        $manager = new AgriculteurManager();
        $manager->validate($agriculteur);
    }

    public function testStatusInvalide()
    {
        $this->expectException(\InvalidArgumentException::class);

        $agriculteur = new Agriculteur();
        $agriculteur->setSuperficieferme(20);
        $agriculteur->setTypeCulture('Tomate');
        $agriculteur->setStatuscompte('invalide');

        $manager = new AgriculteurManager();
        $manager->validate($agriculteur);
    }
}