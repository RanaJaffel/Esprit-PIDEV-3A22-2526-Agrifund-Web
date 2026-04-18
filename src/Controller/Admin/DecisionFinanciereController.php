<?php

namespace App\Controller\Admin;

use App\Entity\DecisionFinanciere;
use App\Form\DecisionFinanciereType;
use App\Repository\DecisionFinanciereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/admin/decision', name: 'admin_decision_')]
class DecisionFinanciereController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        DecisionFinanciereRepository $repo,
        \App\Repository\UtilisateurRepository $utilisateurRepository,
        \App\Repository\AgriculteurRepository $agriculteurRepository,
        \App\Repository\BanqueRepository $banqueRepository,
        \App\Repository\DocumentRepository $documentRepository
    ): Response {
        $statsUtilisateurs     = $utilisateurRepository->countByType();
        $statsAgriculteurs     = $agriculteurRepository->getStatistics();
        $statsBanques          = $banqueRepository->getStatistics();
        $statsDocuments        = $documentRepository->getStatistics();
        $documentsEnAttente    = $documentRepository->findPendingDocuments();
        $agriculteursEnAttente = $agriculteurRepository->findPendingVerification();
        $banquesEnAttente      = $banqueRepository->findPendingVerification();
        $utilisateursRecents   = $utilisateurRepository->findRecentUsers(5);

        return $this->render('admin/decision/index.html.twig', [
            'decisions'               => $repo->findAll(),
            'stats_utilisateurs'      => $statsUtilisateurs,
            'stats_agriculteurs'      => $statsAgriculteurs,
            'stats_banques'           => $statsBanques,
            'stats_documents'         => $statsDocuments,
            'documents_en_attente'    => $documentsEnAttente,
            'agriculteurs_en_attente' => $agriculteursEnAttente,
            'banques_en_attente'      => $banquesEnAttente,
            'utilisateurs_recents'    => $utilisateursRecents,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $decision = new DecisionFinanciere();
        $form     = $this->createForm(DecisionFinanciereType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($decision);
            $em->flush();
            $this->addFlash('success', 'Decision creee !');
            return $this->redirectToRoute('admin_decision_index');
        }

        return $this->render('admin/decision/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Request $request, DecisionFinanciere $decision, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DecisionFinanciereType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Decision modifiee !');
            return $this->redirectToRoute('admin_decision_index');
        }

        return $this->render('admin/decision/edit.html.twig', [
            'form'     => $form->createView(),
            'decision' => $decision,
        ]);
    }

    #[Route('/{id}/show', name: 'show')]
    public function show(DecisionFinanciere $decision): Response
    {
        return $this->render('admin/decision/show.html.twig', [
            'decision' => $decision,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, DecisionFinanciere $decision, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $decision->getIdDecision(), $request->request->get('_token'))) {
            $em->remove($decision);
            $em->flush();
            $this->addFlash('success', 'Decision supprimee !');
        }

        return $this->redirectToRoute('admin_decision_index');
    }

    // ==================== GET EVALUATION ====================
    #[Route('/evaluation/{id}', name: 'get_evaluation', methods: ['GET'])]
    public function getEvaluation(\App\Entity\EvaluationRisque $evaluation): JsonResponse
    {
        return $this->json([
            'id'             => $evaluation->getIdEvaluation(),
            'niveauRisque'   => $evaluation->getNiveauRisque(),
            'scoreGlobal'    => $evaluation->getScoreGlobal(),
            'recommandation' => $evaluation->getRecommandation(),
            'fiabilite'      => $evaluation->getFiabiliteDonnees(),
            'facteur'        => $evaluation->getFacteurPrincipal(),
        ]);
    }

    // ==================== CHATBOT AGRICOLE (GROQ) ====================
    #[Route('/chat', name: 'chat', methods: ['POST'])]
    public function chat(Request $request, HttpClientInterface $client): JsonResponse
    {
        $data       = json_decode($request->getContent(), true);
        $message    = trim($data['message'] ?? '');
        $evaluation = $data['evaluation'] ?? [];

        if (empty($message) || empty($evaluation)) {
            return $this->json(['reply' => "Je n'ai pas recu la question ou l'evaluation."], 400);
        }

        $groqKey = $_ENV['GROQ_API_KEY'] ?? null;

        if (!$groqKey) {
            return $this->json(['reply' => "La cle API Groq n'est pas configuree dans le .env."]);
        }

        $niveauRisque  = $evaluation['niveauRisque']  ?? 'N/A';
        $scoreGlobal   = $evaluation['scoreGlobal']   ?? 'N/A';
        $fiabilite     = $evaluation['fiabilite']     ?? 'N/A';
        $facteur       = $evaluation['facteur']       ?? 'N/A';
        $recommandation= $evaluation['recommandation']?? 'N/A';

        $systemContent = "Tu es un expert agricole senior francophone avec 20 ans d'experience. "
                       . "Tu analyses des projets agricoles et donnes des conseils professionnels, "
                       . "clairs et bienveillants. Tu reponds TOUJOURS en francais, "
                       . "en 3 a 4 phrases maximum. Tu es direct et concret.";

        $userContent = "Voici l'evaluation du projet agricole :\n"
                     . "- Niveau de risque  : " . $niveauRisque   . "\n"
                     . "- Score global      : " . $scoreGlobal    . "/100\n"
                     . "- Fiabilite donnees : " . $fiabilite      . "\n"
                     . "- Facteur principal : " . $facteur        . "\n"
                     . "- Recommandation    : " . $recommandation . "\n\n"
                     . "Ma question : "         . $message;

        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'max_tokens'  => 400,
            'temperature' => 0.7,
            'messages'    => [
                ['role' => 'system', 'content' => $systemContent],
                ['role' => 'user',   'content' => $userContent],
            ],
        ];

        try {
            $response = $client->request(
                'POST',
                'https://api.groq.com/openai/v1/chat/completions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $groqKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json'    => $payload,
                    'timeout' => 20,
                ]
            );

            $content = $response->toArray(false);

            if (isset($content['error'])) {
                $errMsg = $content['error']['message'] ?? json_encode($content['error']);
                error_log('[ChatBot Groq] Erreur API : ' . $errMsg);
                return $this->json(['reply' => "Erreur Groq : " . $errMsg]);
            }

            $reply = $content['choices'][0]['message']['content'] ?? null;

            if (!$reply) {
                return $this->json(['reply' => "Pas de reponse generee. Reessayez."]);
            }

            return $this->json(['reply' => trim($reply)]);

        } catch (\Symfony\Component\HttpClient\Exception\TimeoutException $e) {
            return $this->json(['reply' => "Timeout. Reessayez dans un instant."]);
        } catch (\Exception $e) {
            error_log('[ChatBot Groq] Exception : ' . $e->getMessage());
            return $this->json(['reply' => "Erreur : " . $e->getMessage()]);
        }
    }
}