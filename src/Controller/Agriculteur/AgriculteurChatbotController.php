<?php

namespace App\Controller\Agriculteur;

use App\Entity\OffreFinanciere;
use App\Entity\ProduitFinancier;
use App\Repository\OffreFinanciereRepository;
use App\Repository\ProduitFinancierRepository;
use App\Service\Chatbot\GeminiAgriculteurChatService;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur/chatbot')]
#[IsGranted('ROLE_AGRICULTEUR')]
class AgriculteurChatbotController extends AbstractController
{
    #[Route('/message', name: 'agriculteur_chatbot_message', methods: ['POST'])]
    public function message(
        Request $request,
        OffreFinanciereRepository $offreRepository,
        ProduitFinancierRepository $produitRepository,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Payload JSON invalide.'], 400);
        }

        $action = strtolower(trim((string) ($payload['action'] ?? '')));
        $message = trim((string) ($payload['message'] ?? ''));
        $contextType = strtolower(trim((string) ($payload['contextType'] ?? '')));
        $title = trim((string) ($payload['title'] ?? ''));
        $summary = trim((string) ($payload['summary'] ?? ''));

        if ($action === '') {
            $action = $this->detectActionFromMessage($message);
        }

        $allowedActions = ['produits_dispo', 'offres_dispo', 'expliquer_offre', 'expliquer_produit', 'bureaux'];
        if (!in_array($action, $allowedActions, true)) {
            return $this->json([
                'error' => 'Action invalide. Actions autorisees: produits_dispo, offres_dispo, expliquer_offre, expliquer_produit.',
            ], 400);
        }

        $remaining = $this->consumeRateLimit($request, $cache);
        if ($remaining < 0) {
            return $this->json([
                'error' => 'Limite atteinte. Reessayez dans quelques instants.',
            ], 429);
        }

        try {
            $answer = match ($action) {
                'produits_dispo' => $this->buildProduitsDisponiblesAnswer($produitRepository),
                'offres_dispo' => $this->buildOffresDisponiblesAnswer($offreRepository),
                'expliquer_offre' => $this->buildExplainOffreAnswer($offreRepository, $contextType, $title, $summary),
                'expliquer_produit' => $this->buildExplainProduitAnswer($produitRepository, $contextType, $title, $summary),
                'bureaux' => $this->buildBureauxAnswer(),
                default => 'Action non prise en charge.',
            };
        } catch (\Throwable $e) {
            $logger->error('Agriculteur chatbot action failed.', [
                'action' => $action,
                'context_type' => $contextType,
                'title' => $title,
                'ip' => $request->getClientIp(),
                'exception' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'Le chatbot est temporairement indisponible. Merci de reessayer.',
            ], 502);
        }

        return $this->json([
            'answer' => $answer,
            'remaining' => $remaining,
        ]);
    }

    #[Route('/explain', name: 'agriculteur_chatbot_explain', methods: ['POST'])]
    public function explain(
        Request $request,
        GeminiAgriculteurChatService $chatService,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        #[Autowire('%kernel.debug%')] bool $isDebug
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Payload JSON invalide.'], 400);
        }

        $question = trim((string) ($payload['question'] ?? ''));
        $contextType = strtolower(trim((string) ($payload['contextType'] ?? '')));
        $title = trim((string) ($payload['title'] ?? ''));
        $summary = trim((string) ($payload['summary'] ?? ''));

        if ($question === '' || mb_strlen($question) < 3) {
            return $this->json(['error' => 'Veuillez saisir une question plus detaillee.'], 400);
        }

        if (!in_array($contextType, ['offre', 'produit'], true)) {
            return $this->json(['error' => 'Le contexte doit etre offre ou produit.'], 400);
        }

        if ($title === '') {
            $title = $contextType === 'offre' ? 'Offre AgriFund' : 'Produit AgriFund';
        }

        if ($summary === '') {
            $summary = 'Informations generales disponibles sur la page courante.';
        }

        if (mb_strlen($question) > 600) {
            return $this->json(['error' => 'Question trop longue (max 600 caracteres).'], 400);
        }

        $summary = mb_substr($summary, 0, 800);

        $remaining = $this->consumeRateLimit($request, $cache);
        if ($remaining < 0) {
            return $this->json([
                'error' => 'Limite atteinte. Reessayez dans quelques instants.',
            ], 429);
        }

        try {
            $answer = $chatService->askForOfferOrProduct($question, $contextType, $title, $summary);
        } catch (\Throwable $e) {
            return $this->buildErrorResponse($e, $request, $logger, $isDebug, $contextType, $title, $question);
        }

        return $this->json([
            'answer' => $answer,
            'remaining' => $remaining,
        ]);
    }

    private function consumeRateLimit(Request $request, CacheItemPoolInterface $cache): int
    {
        $user = $this->getUser();
        $userPart = method_exists($user, 'getId') ? (string) $user->getId() : 'anonymous';
        $ipPart = (string) ($request->getClientIp() ?? 'unknown');
        $limiterKey = 'agrichat_' . sha1($userPart . '|' . $ipPart);

        $item = $cache->getItem($limiterKey);
        $bucket = $item->isHit() ? $item->get() : null;
        $now = time();
        $windowSeconds = 60;
        $maxRequests = 8;

        if (!is_array($bucket) || !isset($bucket['start'], $bucket['count']) || ($now - (int) $bucket['start']) >= $windowSeconds) {
            $bucket = ['start' => $now, 'count' => 0];
        }

        if ((int) $bucket['count'] >= $maxRequests) {
            return -1;
        }

        $bucket['count'] = (int) $bucket['count'] + 1;
        $item->set($bucket);
        $item->expiresAfter($windowSeconds);
        $cache->save($item);

        return max(0, $maxRequests - (int) $bucket['count']);
    }

    private function buildErrorResponse(
        \Throwable $e,
        Request $request,
        LoggerInterface $logger,
        bool $isDebug,
        string $contextType,
        string $title,
        string $question
    ): JsonResponse {
        $errorMessage = $e->getMessage();
        $publicError = 'Le chatbot est temporairement indisponible. Merci de reessayer.';
        $isQuotaError = false;

        $lowerError = mb_strtolower($errorMessage);
        if (str_contains($lowerError, 'quota') || str_contains($lowerError, 'resource_exhausted') || str_contains($lowerError, 'http 429')) {
            $publicError = 'Quota Gemini depasse. Merci de reessayer plus tard ou de verifier la facturation/quotas du projet API.';
            $isQuotaError = true;
        }

        $logger->error('Agriculteur chatbot call failed.', [
            'context_type' => $contextType,
            'title' => $title,
            'user' => method_exists((object) $this->getUser(), 'getUserIdentifier') ? $this->getUser()?->getUserIdentifier() : null,
            'ip' => $request->getClientIp(),
            'exception' => $errorMessage,
        ]);

        if ($isQuotaError) {
            return $this->json([
                'answer' => $this->buildFallbackAnswer($contextType, $title, $question),
                'warning' => $publicError,
                'fallback' => true,
                'details' => ($isDebug || $this->isGranted('ROLE_ADMIN')) ? $errorMessage : null,
            ]);
        }

        return $this->json([
            'error' => $publicError,
            'details' => ($isDebug || $this->isGranted('ROLE_ADMIN')) ? $errorMessage : null,
        ], 502);
    }

    private function buildFallbackAnswer(string $contextType, string $title, string $question): string
    {
        $contextLabel = $contextType === 'offre' ? 'offre' : ($contextType === 'produit' ? 'produit' : 'financement');

        return sprintf(
            "Mode secours actif (quota IA temporairement indisponible).\n" .
            "Pour %s '%s', voici une orientation rapide:\n" .
            "1) Verifiez le prix, le taux et les conditions d'eligibilite.\n" .
            "2) Comparez avec 1-2 alternatives avant de postuler.\n" .
            "3) Preparez les pieces justificatives (identite, revenus, activite).\n" .
            "Question detectee: %s\n" .
            "Recommandation: ouvrez la fiche detaillee et confirmez le montant total payable avant soumission.",
            $contextLabel,
            $title,
            $question
        );
    }

    private function detectActionFromMessage(string $message): string
    {
        $msg = mb_strtolower($message);

        if (str_contains($msg, 'produit') && str_contains($msg, 'dispo')) {
            return 'produits_dispo';
        }
        if (str_contains($msg, 'offre') && str_contains($msg, 'dispo')) {
            return 'offres_dispo';
        }
        if (str_contains($msg, 'expliquer') && str_contains($msg, 'offre')) {
            return 'expliquer_offre';
        }
        if (str_contains($msg, 'expliquer') && str_contains($msg, 'produit')) {
            return 'expliquer_produit';
        }
        if (str_contains($msg, 'bureau') || str_contains($msg, 'agence')) {
            return 'bureaux';
        }

        return '';
    }

    private function buildProduitsDisponiblesAnswer(ProduitFinancierRepository $produitRepository): string
    {
        $produits = $produitRepository->findLimited(8);
        if ($produits === []) {
            return 'Aucun produit financier disponible pour le moment.';
        }

        $lines = ["Produits disponibles:"];
        foreach ($produits as $produit) {
            $lines[] = sprintf(
                "- %s (%s) | Taux: %.2f%% | Montant: %s DT | Prix fixe: %s DT",
                (string) $produit->getNomProduit(),
                (string) $produit->getTypeFinancement(),
                (float) ($produit->getTauxInteret() ?? 0),
                number_format((float) ($produit->getMontant() ?? 0), 0, ',', ' '),
                number_format((float) $produit->getPrixFixe(), 2, ',', ' ')
            );
        }

        return implode("\n", $lines);
    }

    private function buildOffresDisponiblesAnswer(OffreFinanciereRepository $offreRepository): string
    {
        $offres = $offreRepository->findActiveOffersLimited(8);
        if ($offres === []) {
            return 'Aucune offre active disponible actuellement.';
        }

        $lines = ["Offres disponibles:"];
        foreach ($offres as $offre) {
            $produit = $offre->getProduitFinancier();
            $lines[] = sprintf(
                "- %s | Prix: %s DT | Produit: %s",
                (string) $offre->getNomOffre(),
                number_format((float) $offre->getPrix(), 2, ',', ' '),
                $produit?->getNomProduit() ?? '-'
            );
        }

        return implode("\n", $lines);
    }

    private function buildExplainOffreAnswer(
        OffreFinanciereRepository $offreRepository,
        string $contextType,
        string $title,
        string $summary
    ): string {
        $offre = null;
        if ($contextType === 'offre' && $title !== '') {
            $offre = $offreRepository->findOneActiveOfferByName($title);
        }

        if (!$offre instanceof OffreFinanciere) {
            $offre = $offreRepository->findFirstActiveOffer();
        }

        if (!$offre instanceof OffreFinanciere) {
            return 'Aucune offre active a expliquer pour le moment.';
        }

        $produit = $offre->getProduitFinancier();
        $conditions = trim((string) $offre->getConditions());
        if ($conditions === '' && $summary !== '') {
            $conditions = $summary;
        }

        return sprintf(
            "Explication de l'offre '%s':\n" .
            "- Prix: %s DT\n" .
            "- Produit associe: %s\n" .
            "- Taux du produit: %.2f%%\n" .
            "- Conditions: %s\n" .
            "Conseil: verifiez que le montant total payable correspond bien a votre capacite avant de postuler.",
            (string) $offre->getNomOffre(),
            number_format((float) $offre->getPrix(), 2, ',', ' '),
            $produit?->getNomProduit() ?? '-',
            (float) ($produit?->getTauxInteret() ?? 0),
            mb_substr($conditions !== '' ? $conditions : 'Consultez la fiche detaillee pour toutes les conditions.', 0, 260)
        );
    }

    private function buildExplainProduitAnswer(
        ProduitFinancierRepository $produitRepository,
        string $contextType,
        string $title,
        string $summary
    ): string {
        $produit = null;
        if ($contextType === 'produit' && $title !== '') {
            $produit = $produitRepository->findOneByName($title);
        }

        if (!$produit instanceof ProduitFinancier) {
            $produit = $produitRepository->findLimited(1)[0] ?? null;
        }

        if (!$produit instanceof ProduitFinancier) {
            return 'Aucun produit disponible a expliquer pour le moment.';
        }

        $regles = trim((string) $produit->getReglesFinancieres());
        if ($regles === '' && $summary !== '') {
            $regles = $summary;
        }

        return sprintf(
            "Explication du produit '%s':\n" .
            "- Type: %s\n" .
            "- Taux d'interet: %.2f%%\n" .
            "- Montant: %s DT\n" .
            "- Prix fixe payable: %s DT\n" .
            "- Regles: %s\n" .
            "Conseil: comparez ce taux avec au moins une autre option avant validation finale.",
            (string) $produit->getNomProduit(),
            (string) $produit->getTypeFinancement(),
            (float) ($produit->getTauxInteret() ?? 0),
            number_format((float) ($produit->getMontant() ?? 0), 0, ',', ' '),
            number_format((float) $produit->getPrixFixe(), 2, ',', ' '),
            mb_substr($regles !== '' ? $regles : 'Consultez la fiche detaillee pour les conditions exactes.', 0, 260)
        );
    }

    private function buildBureauxAnswer(): string
    {
        return "Bureaux AgriFund en Tunisie:\n"
            . "- Tunis (Siege): Avenue Habib Bourguiba, Tunis\n"
            . "- Sousse: Avenue du 14 Janvier, Sousse\n"
            . "- Sfax: Avenue Hedi Chaker, Sfax\n"
            . "- Nabeul: Avenue Habib Thameur, Nabeul\n"
            . "- Beja: Rue de la Republique, Beja\n"
            . "- Tozeur: Avenue Farhat Hached, Tozeur";
    }
}
