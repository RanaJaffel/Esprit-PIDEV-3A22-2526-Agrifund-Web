<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Component\HttpFoundation\Response;

final class ApiOffreControllerTest extends ApiControllerTestCase
{
    public function testListAndShowArePublic(): void
    {
        $produit = $this->createProduit('Produit Pour Offre');
        $offre = $this->createOffre($produit, 'Offre Publique');

        $this->client->request('GET', '/api/offres');
        self::assertResponseIsSuccessful();

        $listPayload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertIsArray($listPayload['data'] ?? null);
        self::assertCount(1, $listPayload['data']);

        $this->client->request('GET', '/api/offres/' . $offre->getId());
        self::assertResponseIsSuccessful();

        $showPayload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Offre Publique', $showPayload['data']['nomOffre'] ?? null);
    }

    public function testAdminCanCreateUpdateAndDeleteOffre(): void
    {
        $this->loginAsAdmin();
        $produit = $this->createProduit('Produit Parent');

        $this->client->request(
            'POST',
            '/api/offres',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'nomOffre' => 'Offre API',
                'conditions' => 'Conditions API',
                'statut' => 'Active',
                'produitId' => $produit->getId(),
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $createdPayload = json_decode((string) $this->client->getResponse()->getContent(), true);
        $id = $createdPayload['data']['id'] ?? null;
        self::assertNotNull($id);

        $this->client->request(
            'PATCH',
            '/api/offres/' . $id,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'statut' => 'En attente',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseIsSuccessful();
        $updatedPayload = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('En attente', $updatedPayload['data']['statut'] ?? null);

        $this->client->request('DELETE', '/api/offres/' . $id);
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testCreateOffreWithUnknownProduitReturns404(): void
    {
        $this->loginAsAdmin();

        $this->client->request(
            'POST',
            '/api/offres',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'nomOffre' => 'Offre KO',
                'conditions' => 'Conditions KO',
                'statut' => 'Active',
                'produitId' => 999999,
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
