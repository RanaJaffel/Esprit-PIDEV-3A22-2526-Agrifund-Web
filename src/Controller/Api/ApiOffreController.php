<?php

namespace App\Controller\Api;

use App\Entity\OffreFinanciere;
use App\Entity\ProduitFinancier;
use App\Repository\OffreFinanciereRepository;
use App\Repository\ProduitFinancierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/offres', name: 'api_offre_')]
class ApiOffreController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(OffreFinanciereRepository $repository): JsonResponse
    {
        $offres = $repository->findAll();

        $data = array_map(fn (OffreFinanciere $offre) => $this->serializeOffer($offre), $offres);

        return $this->json(['data' => $data]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(?OffreFinanciere $offre): JsonResponse
    {
        if (!$offre) {
            return $this->json(['message' => 'Offre non trouvee'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['data' => $this->serializeOffer($offre)]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        ProduitFinancierRepository $produitRepository,
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

        $errors = $this->validateOfferPayload($payload, true);
        if (!empty($errors)) {
            return $this->json([
                'message' => 'Validation echouee',
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $produit = $this->findProduit($produitRepository, $payload);
        if (!$produit) {
            return $this->json(['message' => 'Produit associe introuvable'], Response::HTTP_NOT_FOUND);
        }

        $offre = new OffreFinanciere();
        $this->hydrateOffer($offre, $payload, $produit);

        $violations = $validator->validate($offre);
        if (count($violations) > 0) {
            return $this->json([
                'message' => 'Validation echouee',
                'errors' => $this->formatViolations($violations),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($offre);
        $entityManager->flush();

        return $this->json(['data' => $this->serializeOffer($offre)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(
        Request $request,
        ?OffreFinanciere $offre,
        ProduitFinancierRepository $produitRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Acces refuse'], Response::HTTP_FORBIDDEN);
        }

        if (!$offre) {
            return $this->json(['message' => 'Offre non trouvee'], Response::HTTP_NOT_FOUND);
        }

        $payload = $this->decodeJson($request);
        if ($payload === null) {
            return $this->json(['message' => 'JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        $isPut = $request->getMethod() === Request::METHOD_PUT;
        $errors = $this->validateOfferPayload($payload, $isPut);
        if (!empty($errors)) {
            return $this->json([
                'message' => 'Validation echouee',
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$isPut && !$this->hasAnyOfferField($payload)) {
            return $this->json(['message' => 'Aucune donnee a mettre a jour'], Response::HTTP_BAD_REQUEST);
        }

        $produit = null;
        if ($isPut || array_key_exists('produitId', $payload)) {
            $produit = $this->findProduit($produitRepository, $payload);
            if (!$produit) {
                return $this->json(['message' => 'Produit associe introuvable'], Response::HTTP_NOT_FOUND);
            }
        }

        $this->hydrateOffer($offre, $payload, $produit, !$isPut);

        $violations = $validator->validate($offre);
        if (count($violations) > 0) {
            return $this->json([
                'message' => 'Validation echouee',
                'errors' => $this->formatViolations($violations),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json(['data' => $this->serializeOffer($offre)]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(?OffreFinanciere $offre, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Acces refuse'], Response::HTTP_FORBIDDEN);
        }

        if (!$offre) {
            return $this->json(['message' => 'Offre non trouvee'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($offre);
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

    private function validateOfferPayload(array $payload, bool $requireAll): array
    {
        $errors = [];

        if ($requireAll || array_key_exists('nomOffre', $payload)) {
            if (!isset($payload['nomOffre']) || trim((string) $payload['nomOffre']) === '') {
                $errors['nomOffre'][] = 'Le nomOffre est obligatoire.';
            }
        }

        if ($requireAll || array_key_exists('statut', $payload)) {
            if (!isset($payload['statut']) || trim((string) $payload['statut']) === '') {
                $errors['statut'][] = 'Le statut est obligatoire.';
            }
        }

        if ($requireAll || array_key_exists('produitId', $payload)) {
            if (!array_key_exists('produitId', $payload) || !is_numeric($payload['produitId'])) {
                $errors['produitId'][] = 'Le produitId doit etre numerique.';
            }
        }

        return $errors;
    }

    private function hasAnyOfferField(array $payload): bool
    {
        foreach (['nomOffre', 'conditions', 'statut', 'produitId'] as $field) {
            if (array_key_exists($field, $payload)) {
                return true;
            }
        }

        return false;
    }

    private function hydrateOffer(OffreFinanciere $offre, array $payload, ?ProduitFinancier $produit, bool $partial = false): void
    {
        if (!$partial || array_key_exists('nomOffre', $payload)) {
            $offre->setNomOffre((string) ($payload['nomOffre'] ?? ''));
        }

        if (!$partial || array_key_exists('conditions', $payload)) {
            $conditions = $payload['conditions'] ?? null;
            $offre->setConditions($conditions !== null ? (string) $conditions : null);
        }

        if (!$partial || array_key_exists('statut', $payload)) {
            $offre->setStatut((string) ($payload['statut'] ?? ''));
        }

        if (($partial && $produit !== null) || !$partial) {
            $offre->setProduitFinancier($produit);
        }
    }

    private function findProduit(ProduitFinancierRepository $produitRepository, array $payload): ?ProduitFinancier
    {
        if (!array_key_exists('produitId', $payload) || !is_numeric($payload['produitId'])) {
            return null;
        }

        return $produitRepository->find((int) $payload['produitId']);
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

    private function serializeOffer(OffreFinanciere $offre): array
    {
        $produit = $offre->getProduitFinancier();

        return [
            'id' => $offre->getId(),
            'nomOffre' => $offre->getNomOffre(),
            'conditions' => $offre->getConditions(),
            'statut' => $offre->getStatut(),
            'produit' => $produit ? [
                'id' => $produit->getId(),
                'nomProduit' => $produit->getNomProduit(),
                'typeFinancement' => $produit->getTypeFinancement(),
                'tauxInteret' => $produit->getTauxInteret(),
                'montant' => $produit->getMontant(),
            ] : null,
        ];
    }
}
