<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Component\HttpFoundation\Response;

final class ApiProduitControllerTest extends ApiControllerTestCase
{
    public function testListAndShowArePublic(): void
    {
        $produit = $this->createProduit('Produit Public');

        $this->client->request('GET', '/api/produits');
        self::assertResponseIsSuccessful();

        $listPayload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertIsArray($listPayload['data'] ?? null);
        self::assertCount(1, $listPayload['data']);

        $this->client->request('GET', '/api/produits/' . $produit->getId());
        self::assertResponseIsSuccessful();

        $showPayload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Produit Public', $showPayload['data']['nomProduit'] ?? null);
    }

    public function testCreateRequiresAdminRole(): void
    {
        $this->client->request(
            'POST',
            '/api/produits',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'nomProduit' => 'Produit Blocage',
                'typeFinancement' => 'Crédit',
                'tauxInteret' => 4.8,
                'montant' => 90000,
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testAdminCanCreateUpdateAndDeleteProduit(): void
    {
        $this->loginAsAdmin();

        $this->client->request(
            'POST',
            '/api/produits',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'nomProduit' => 'Produit API',
                'typeFinancement' => 'Crédit',
                'tauxInteret' => 6.2,
                'montant' => 120000,
                'reglesFinancieres' => 'Regles API',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $createdPayload = json_decode((string) $this->client->getResponse()->getContent(), true);
        $id = $createdPayload['data']['id'] ?? null;
        self::assertNotNull($id);

        $this->client->request(
            'PATCH',
            '/api/produits/' . $id,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'nomProduit' => 'Produit API MAJ',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseIsSuccessful();
        $updatedPayload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Produit API MAJ', $updatedPayload['data']['nomProduit'] ?? null);

        $this->client->request('DELETE', '/api/produits/' . $id);
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
