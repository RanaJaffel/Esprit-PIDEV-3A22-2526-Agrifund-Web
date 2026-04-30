<?php

namespace App\Tests\Service;

use App\Entity\Capteur;
use App\Service\CapteurManager;
use PHPUnit\Framework\TestCase;

class CapteurManagerTest extends TestCase
{
    public function testCapteurValide()
    {
        $capteur = new Capteur();
        $capteur->setIdproject(1);
        $capteur->setTypeCapteur('TEMPERATURE');
        $capteur->setDateInstallation(new \DateTimeImmutable('2024-01-01'));

        $manager = new CapteurManager();

        $this->assertTrue($manager->validate($capteur));
    }

    public function testCapteurSansProjet()
    {
        $this->expectException(\InvalidArgumentException::class);

        $capteur = new Capteur();
        $capteur->setTypeCapteur('TEMPERATURE');
        $capteur->setDateInstallation(new \DateTimeImmutable('2024-01-01'));

        $manager = new CapteurManager();
        $manager->validate($capteur);
    }

    public function testTypeInvalide()
    {
        $this->expectException(\InvalidArgumentException::class);

        $capteur = new Capteur();
        $capteur->setIdproject(1);
        $capteur->setTypeCapteur('PRESSION'); // ❌ invalide
        $capteur->setDateInstallation(new \DateTimeImmutable('2024-01-01'));

        $manager = new CapteurManager();
        $manager->validate($capteur);
    }

    public function testDateInstallationFuture()
    {
        $this->expectException(\InvalidArgumentException::class);

        $capteur = new Capteur();
        $capteur->setIdproject(1);
        $capteur->setTypeCapteur('TEMPERATURE');
        $capteur->setDateInstallation(new \DateTimeImmutable('+1 year'));

        $manager = new CapteurManager();
        $manager->validate($capteur);
    }
}
