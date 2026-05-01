<?php

namespace App\Controller\Api;

use App\Entity\ProduitFinancier;
use App\Repository\ProduitFinancierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/produits', name: 'api_produit_')]
class ApiProduitController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ProduitFinancierRepository $repository): JsonResponse
    {
        $produits = $repository->findAll();

        $data = array_map(fn (ProduitFinancier $produit) => $this->serializeProduct($produit, false), $produits);

        return $this->json(['data' => $data]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(?ProduitFinancier $produit): JsonResponse
    {
        if (!$produit) {
            return $this->json(['message' => 'Produit non trouve'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['data' => $this->serializeProduct($produit, true)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Acces refuse'], Response::HTTP_FORBIDDEN);
        }

        $payload = $this->decodeJson($request);
        if ($payload === null) {
            return $this->json(['message' => 'JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validateProductPayload($payload, true);
        if (!empty($errors)) {
            return $this->json([
                'message' => 'Validation echouee',
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $produit = new ProduitFinancier();
        $this->hydrateProduct($produit, $payload);

        $violations = $validator->validate($produit);
        if (count($violations) > 0) {
            return $this->json([
                'message' => 'Validation echouee',
                'errors' => $this->formatViolations($violations),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($produit);
        $entityManager->flush();

        return $this->json(['data' => $this->serializeProduct($produit, true)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(
        Request $request,
        ?ProduitFinancier $produit,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Acces refuse'], Response::HTTP_FORBIDDEN);
        }

        if (!$produit) {
            return $this->json(['message' => 'Produit non trouve'], Response::HTTP_NOT_FOUND);
        }

        $payload = $this->decodeJson($request);
        if ($payload === null) {
            return $this->json(['message' => 'JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        $isPut = $request->getMethod() === Request::METHOD_PUT;
        $errors = $this->validateProductPayload($payload, $isPut);
        if (!empty($errors)) {
            return $this->json([
                'message' => 'Validation echouee',
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$isPut && !$this->hasAnyProductField($payload)) {
            return $this->json(['message' => 'Aucune donnee a mettre a jour'], Response::HTTP_BAD_REQUEST);
        }

        $this->hydrateProduct($produit, $payload, !$isPut);

        $violations = $validator->validate($produit);
        if (count($violations) > 0) {
            return $this->json([
                'message' => 'Validation echouee',
                'errors' => $this->formatViolations($violations),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json(['data' => $this->serializeProduct($produit, true)]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(?ProduitFinancier $produit, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Acces refuse'], Response::HTTP_FORBIDDEN);
        }

        if (!$produit) {
            return $this->json(['message' => 'Produit non trouve'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function decodeJson(Request $request): ?array
    {
        $content = trim((string) $request->getContent());
        if ($content === '') {
            return [];
        }

        try {
            $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($payload) ? $payload : null;
    }

    private function validateProductPayload(array $payload, bool $requireAll): array
    {
        $errors = [];

        if ($requireAll || array_key_exists('nomProduit', $payload)) {
            if (!isset($payload['nomProduit']) || trim((string) $payload['nomProduit']) === '') {
                $errors['nomProduit'][] = 'Le nomProduit est obligatoire.';
            }
        }

        if ($requireAll || array_key_exists('typeFinancement', $payload)) {
            if (!isset($payload['typeFinancement']) || trim((string) $payload['typeFinancement']) === '') {
                $errors['typeFinancement'][] = 'Le typeFinancement est obligatoire.';
            }
        }

        if ($requireAll || array_key_exists('tauxInteret', $payload)) {
            if (!array_key_exists('tauxInteret', $payload) || !is_numeric($payload['tauxInteret'])) {
                $errors['tauxInteret'][] = 'Le tauxInteret doit etre numerique.';
            }
        }

        if ($requireAll || array_key_exists('montant', $payload)) {
            if (!array_key_exists('montant', $payload) || !is_numeric($payload['montant'])) {
                $errors['montant'][] = 'Le montant doit etre numerique.';
            }
        }

        return $errors;
    }

    private function hasAnyProductField(array $payload): bool
    {
        foreach (['nomProduit', 'typeFinancement', 'tauxInteret', 'montant', 'reglesFinancieres'] as $field) {
            if (array_key_exists($field, $payload)) {
                return true;
            }
        }

        return false;
    }

    private function hydrateProduct(ProduitFinancier $produit, array $payload, bool $partial = false): void
    {
        if (!$partial || array_key_exists('nomProduit', $payload)) {
            $produit->setNomProduit((string) ($payload['nomProduit'] ?? ''));
        }

        if (!$partial || array_key_exists('typeFinancement', $payload)) {
            $produit->setTypeFinancement((string) ($payload['typeFinancement'] ?? ''));
        }

        if (!$partial || array_key_exists('tauxInteret', $payload)) {
            $produit->setTauxInteret((float) ($payload['tauxInteret'] ?? 0));
        }

        if (!$partial || array_key_exists('montant', $payload)) {
            $produit->setMontant((float) ($payload['montant'] ?? 0));
        }

        if (!$partial || array_key_exists('reglesFinancieres', $payload)) {
            $regles = $payload['reglesFinancieres'] ?? null;
            $produit->setReglesFinancieres($regles !== null ? (string) $regles : null);
        }
    }

    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $field = $violation->getPropertyPath() !== '' ? $violation->getPropertyPath() : 'global';
            $errors[$field][] = $violation->getMessage();
        }

        return $errors;
    }

    private function serializeProduct(ProduitFinancier $produit, bool $withOffers): array
    {
        $data = [
            'id' => $produit->getId(),
            'nomProduit' => $produit->getNomProduit(),
            'typeFinancement' => $produit->getTypeFinancement(),
            'tauxInteret' => $produit->getTauxInteret(),
            'montant' => $produit->getMontant(),
            'reglesFinancieres' => $produit->getReglesFinancieres(),
            'offresCount' => $produit->getOffres()->count(),
        ];

        if ($withOffers) {
            $data['offres'] = array_map(static fn ($offre) => [
                'id' => $offre->getId(),
                'nomOffre' => $offre->getNomOffre(),
                'statut' => $offre->getStatut(),
                'conditions' => $offre->getConditions(),
            ], $produit->getOffres()->toArray());
        }

        return $data;
    }
}
