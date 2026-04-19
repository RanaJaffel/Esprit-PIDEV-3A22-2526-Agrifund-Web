<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\OffreFinanciere;
use App\Entity\ProduitFinancier;
use App\Entity\Utilisateur;
use App\Payment\AchatStatus;
use App\Payment\PaymentProvider;
use App\Payment\PaymentStatus;
use App\Service\Payment\OfferCheckoutPreparationService;
use PHPUnit\Framework\TestCase;

class OfferCheckoutPreparationServiceTest extends TestCase
{
    public function testPrepareIgnoresClientSubmittedAmount(): void
    {
        $service = new OfferCheckoutPreparationService();

        $utilisateur = (new Utilisateur())
            ->setNom('Test')
            ->setPrenom('User')
            ->setEmail('test-user@example.com')
            ->setPassword('secret');

        $produit = (new ProduitFinancier())
            ->setNomProduit('Produit Test')
            ->setTypeFinancement('Crédit')
            ->setTauxInteret(8.5)
                ->setMontant(100000)
            ->setReglesFinancieres('Regles test');

        $offre = (new OffreFinanciere())
            ->setNomOffre('Offre Speciale')
            ->setStatut('Active')
            ->setProduitFinancier($produit)
            ->setPrix('149.99');

        [$achat, $transaction] = $service->prepare($utilisateur, $offre, '1.00');

        $breakdown = $service->computeBreakdown(149.99);

        $this->assertSame($breakdown['total'], $achat->getMontant());
        $this->assertSame($breakdown['total'], $transaction->getMontant());
        $this->assertSame(AchatStatus::PENDING_GATEWAY, $achat->getStatut());
        $this->assertSame(PaymentStatus::PENDING, $transaction->getStatut());
        $this->assertSame(PaymentProvider::MANUAL, $transaction->getProvider());
        $this->assertNotNull($achat->getReference());
        $this->assertNotNull($transaction->getReference());
    }

    public function testPrepareRejectsInactiveOffer(): void
    {
        $service = new OfferCheckoutPreparationService();

        $utilisateur = (new Utilisateur())
            ->setNom('Test')
            ->setPrenom('User')
            ->setEmail('test-user2@example.com')
            ->setPassword('secret');

        $produit = (new ProduitFinancier())
            ->setNomProduit('Produit Test')
            ->setTypeFinancement('Crédit')
            ->setTauxInteret(8.5)
                ->setMontant(100000)
            ->setReglesFinancieres('Regles test');

        $offre = (new OffreFinanciere())
            ->setNomOffre('Offre inactive')
            ->setStatut('Cancelled')
            ->setProduitFinancier($produit)
            ->setPrix('149.99');

        $this->expectException(\InvalidArgumentException::class);

        $service->prepare($utilisateur, $offre, '5000.00');
    }
}
