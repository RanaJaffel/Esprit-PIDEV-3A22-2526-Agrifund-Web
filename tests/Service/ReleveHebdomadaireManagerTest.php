<?php

namespace App\Tests\Service;

use App\Entity\ReleveHebdomadaire;
use App\Service\ReleveHebdomadaireManager;
use PHPUnit\Framework\TestCase;

class ReleveHebdomadaireManagerTest extends TestCase
{
    public function testValidReleve()
    {
        $releve = new ReleveHebdomadaire();
        $releve->setDateDebut(new \DateTime('2026-04-01'));
        $releve->setDateFin(new \DateTime('2026-04-08'));
        $releve->setTempMoyenne(25);
        $releve->setHumiditeMoyenne(60);

        $manager = new ReleveHebdomadaireManager();

        $this->assertTrue($manager->validate($releve));
    }

    public function testInvalidDate()
    {
        $this->expectException(\InvalidArgumentException::class);

        $releve = new ReleveHebdomadaire();
        $releve->setDateDebut(new \DateTime('2026-04-08'));
        $releve->setDateFin(new \DateTime('2026-04-01'));
        $releve->setTempMoyenne(25);
        $releve->setHumiditeMoyenne(60);

        $manager = new ReleveHebdomadaireManager();
        $manager->validate($releve);
    }

    public function testInvalidTemperature()
    {
        $this->expectException(\InvalidArgumentException::class);

        $releve = new ReleveHebdomadaire();
        $releve->setDateDebut(new \DateTime('2026-04-01'));
        $releve->setDateFin(new \DateTime('2026-04-08'));
        $releve->setTempMoyenne(100); // ❌ invalide
        $releve->setHumiditeMoyenne(60);

        $manager = new ReleveHebdomadaireManager();
        $manager->validate($releve);
    }

    public function testInvalidHumidity()
    {
        $this->expectException(\InvalidArgumentException::class);

        $releve = new ReleveHebdomadaire();
        $releve->setDateDebut(new \DateTime('2026-04-01'));
        $releve->setDateFin(new \DateTime('2026-04-08'));
        $releve->setTempMoyenne(25);
        $releve->setHumiditeMoyenne(150); // ❌ invalide

        $manager = new ReleveHebdomadaireManager();
        $manager->validate($releve);
    }
}