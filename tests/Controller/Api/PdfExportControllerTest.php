<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Component\HttpFoundation\Response;

final class PdfExportControllerTest extends ApiControllerTestCase
{
    public function testPublicProductDetailPdfRendersAsPdf(): void
    {
        $produit = $this->createProduit('Produit PDF Public');
        $this->createOffre($produit, 'Offre PDF Public');

        $this->client->request('GET', '/produit/' . $produit->getId() . '/pdf');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/pdf');
        self::assertStringStartsWith('%PDF', (string) $this->client->getResponse()->getContent());
    }

    public function testAdminProductAndOfferPdfExportsRenderAsPdf(): void
    {
        $this->loginAsAdmin();

        $produit = $this->createProduit('Produit Export PDF');
        $this->createOffre($produit, 'Offre Export PDF');

        $this->client->request('GET', '/admin/produits/export-pdf');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/pdf');
        self::assertStringStartsWith('%PDF', (string) $this->client->getResponse()->getContent());

        $this->client->request('GET', '/admin/offres/export-pdf');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/pdf');
        self::assertStringStartsWith('%PDF', (string) $this->client->getResponse()->getContent());
    }

    public function testAnonymousAdminPdfExportIsProtected(): void
    {
        $this->client->request('GET', '/admin/produits/export-pdf');

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }
}
