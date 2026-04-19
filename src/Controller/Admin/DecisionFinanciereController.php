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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

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
    public function new(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $decision = new DecisionFinanciere();
        $form     = $this->createForm(DecisionFinanciereType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($decision);
            $em->flush();
            $this->sendDecisionEmail($mailer, $decision);
            $this->addFlash('success', 'Decision creee et email envoye !');
            return $this->redirectToRoute('admin_decision_index');
        }

        return $this->render('admin/decision/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idDecision}/edit', name: 'edit')]
    public function edit(Request $request, DecisionFinanciere $decision, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $form = $this->createForm(DecisionFinanciereType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->sendDecisionEmail($mailer, $decision);
            $this->addFlash('success', 'Decision modifiee et email envoye !');
            return $this->redirectToRoute('admin_decision_index');
        }

        return $this->render('admin/decision/edit.html.twig', [
            'form'     => $form->createView(),
            'decision' => $decision,
        ]);
    }

    #[Route('/{idDecision}/show', name: 'show')]
    public function show(DecisionFinanciere $decision): Response
    {
        $qrContent = sprintf(
            "Decision #%d | Statut: %s | Score: %d/100 | Risque: %s | Date: %s",
            $decision->getIdDecision(),
            strtoupper($decision->getStatut()),
            $decision->getEvaluation()?->getScoreGlobal() ?? 0,
            $decision->getEvaluation()?->getNiveauRisque() ?? 'N/A',
            $decision->getDateDecision()?->format('d/m/Y') ?? ''
        );

        $qrCode = new QrCode(
            data: $qrContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 200,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(10, 61, 31),
            backgroundColor: new Color(255, 255, 255),
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg  = $result->getString();

        return $this->render('admin/decision/show.html.twig', [
            'decision' => $decision,
            'qrSvg'    => $qrSvg,
        ]);
    }

    #[Route('/{idDecision}/qrcode', name: 'qrcode', methods: ['GET'])]
    public function qrcode(DecisionFinanciere $decision): Response
    {
        $qrContent = sprintf(
            "Decision #%d | Statut: %s | Score: %d/100 | Risque: %s",
            $decision->getIdDecision(),
            strtoupper($decision->getStatut()),
            $decision->getEvaluation()?->getScoreGlobal() ?? 0,
            $decision->getEvaluation()?->getNiveauRisque() ?? 'N/A'
        );

        $qrCode = new QrCode(
            data: $qrContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(10, 61, 31),
            backgroundColor: new Color(255, 255, 255),
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);

        return new Response(
            $result->getString(),
            200,
            ['Content-Type' => 'image/svg+xml']
        );
    }

    #[Route('/{idDecision}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, DecisionFinanciere $decision, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $decision->getIdDecision(), $request->request->get('_token'))) {
            $em->remove($decision);
            $em->flush();
            $this->addFlash('success', 'Decision supprimee !');
        }
        return $this->redirectToRoute('admin_decision_index');
    }

    // ==================== EMAIL ====================
    private function sendDecisionEmail(MailerInterface $mailer, DecisionFinanciere $decision): void
    {
        $statut      = $decision->getStatut();
        $evaluation  = $decision->getEvaluation();
        $evalId      = $evaluation?->getIdEvaluation() ?? 'N/A';
        $score       = $evaluation?->getScoreGlobal()  ?? 'N/A';
        $risque      = $evaluation?->getNiveauRisque()  ?? 'N/A';
        $fiabilite   = $evaluation?->getFiabiliteDonnees() ?? 'N/A';
        $facteur     = $evaluation?->getFacteurPrincipal()  ?? 'N/A';
        $justif      = $decision->getJustification() ?? '';
        $date        = $decision->getDateDecision()?->format('d/m/Y a H:i') ?? date('d/m/Y');
        $estApprouve = ($statut === 'approuve');

        $sujet = $estApprouve
            ? '[AgriFund] Votre financement agricole est APPROUVE'
            : '[AgriFund] Resultat de votre demande de financement';

        $resultat = $estApprouve ? 'APPROUVEE' : 'REFUSEE';
        $intro    = $estApprouve
            ? "Nous avons le plaisir de vous informer que votre demande a ete APPROUVEE.\nNotre equipe vous contactera prochainement."
            : "Nous avons le regret de vous informer que votre demande a ete REFUSEE.\nVous pouvez retravailler votre dossier et soumettre une nouvelle demande.\n\nConseils :\n- Ameliorez la fiabilite de vos donnees\n- Renforcez les points faibles identifies\n- Consultez un expert agricole si besoin";

        $corps = "Bonjour,\n\n"
               . "========================================\n"
               . "  DECISION FINANCIERE - AGRIFUND\n"
               . "  RESULTAT : " . $resultat . "\n"
               . "========================================\n\n"
               . $intro . "\n\n"
               . "----------------------------------------\n"
               . "Details :\n"
               . "- Evaluation ID     : #" . $evalId   . "\n"
               . "- Score global      : " . $score     . "/100\n"
               . "- Niveau de risque  : " . $risque    . "\n"
               . "- Fiabilite         : " . $fiabilite . "\n"
               . "- Facteur principal : " . $facteur   . "\n"
               . "- Date de decision  : " . $date      . "\n\n"
               . "Justification :\n" . $justif . "\n\n"
               . "----------------------------------------\n"
               . "Cordialement,\nL'equipe AgriFund\n";

        try {
            $email = (new Email())
                ->from('chedyderouiche87@gmail.com')
                ->to('chedyderouiche87@gmail.com')
                ->subject($sujet)
                ->text($corps);

            $mailer->send($email);
            error_log('[Mailer] Email envoye - statut=' . $statut);
        } catch (\Exception $e) {
            error_log('[Mailer] ERREUR : ' . $e->getMessage());
        }
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

    // ==================== CHATBOT (GROQ) ====================
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

        $niveauRisque   = $evaluation['niveauRisque']   ?? 'N/A';
        $scoreGlobal    = $evaluation['scoreGlobal']    ?? 'N/A';
        $fiabilite      = $evaluation['fiabilite']      ?? 'N/A';
        $facteur        = $evaluation['facteur']        ?? 'N/A';
        $recommandation = $evaluation['recommandation'] ?? 'N/A';

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

        try {
            $response = $client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $groqKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'llama-3.3-70b-versatile',
                    'max_tokens'  => 400,
                    'temperature' => 0.7,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemContent],
                        ['role' => 'user',   'content' => $userContent],
                    ],
                ],
                'timeout' => 20,
            ]);

            $content = $response->toArray(false);

            if (isset($content['error'])) {
                $errMsg = $content['error']['message'] ?? json_encode($content['error']);
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