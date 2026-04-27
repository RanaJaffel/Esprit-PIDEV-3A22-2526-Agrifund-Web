<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Achat;
use App\Entity\TransactionPaiement;
use App\Entity\Utilisateur;
use App\Payment\AchatStatus;
use App\Payment\PaymentProvider;
use App\Payment\PaymentStatus;
use App\Tests\Controller\Api\ApiControllerTestCase;

final class AdminPaymentIndexRenderTest extends ApiControllerTestCase
{
    public function testAdminPaymentValidationPageRendersPaymentQueue(): void
    {
        $this->loginAsAdmin();

        $customer = (new Utilisateur())
            ->setNom('Client')
            ->setPrenom('Demo')
            ->setEmail('client-demo@example.test')
            ->setPassword('password');

        $produit = $this->createProduit('Produit Paiement');

        $achat = (new Achat())
            ->setReference('ACH-TEST-001')
            ->setUtilisateur($customer)
            ->setProduit($produit)
            ->setTypeCible(Achat::TYPE_PRODUCT)
            ->setMontant('120.00')
            ->setMontantBase('100.00')
            ->setMontantTaxe('20.00')
            ->setStatut(AchatStatus::PENDING_GATEWAY);

        $transaction = (new TransactionPaiement())
            ->setReference('TX-TEST-001')
            ->setAchat($achat)
            ->setProvider(PaymentProvider::MANUAL)
            ->setPaymentMethod(TransactionPaiement::METHOD_MANUAL)
            ->setMontant('120.00')
            ->setStatut(PaymentStatus::PENDING)
            ->setVerificationStatus('pending')
            ->setGatewayPayload(['proof_path' => 'uploads/payment-proofs/demo.pdf']);

        $this->entityManager->persist($customer);
        $this->entityManager->persist($achat);
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        $this->client->request('GET', '/admin/paiements');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Validation des paiements');
        self::assertAnySelectorTextContains('.payment-card', 'TX-TEST-001');
        self::assertAnySelectorTextContains('.payment-card', 'Produit Paiement');
    }

    public function testLegacyUnreadCountEndpointReturnsJsonForAuthenticatedAdmin(): void
    {
        $this->loginAsAdmin();

        $this->client->request('GET', '/api/messages/unread-count');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');
        self::assertSame(['count' => 0], json_decode($this->client->getResponse()->getContent(), true));
    }
}
