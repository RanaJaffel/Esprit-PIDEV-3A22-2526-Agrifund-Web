<?php

namespace App\Controller\Agriculteur;

use App\Entity\ProduitFinancier;
use App\Entity\TransactionPaiement;
use App\Entity\Utilisateur;
use App\Payment\PaymentProvider;
use App\Repository\DocumentRepository;
use App\Repository\MessageRepository;
use App\Repository\OffreFinanciereRepository;
use App\Repository\ProduitFinancierRepository;
use App\Service\Payment\OfferCheckoutPreparationService;
use App\Service\Application\ApplicationNotificationService;
use App\Service\PdfService;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur')]
#[IsGranted('ROLE_AGRICULTEUR')]
class AgriculteurDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'agriculteur_dashboard')]
    public function index(
        DocumentRepository $documentRepository,
        MessageRepository $messageRepository
    ): Response {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Vous devez etre connecte.');
        }

        $agriculteur = $currentUser->getAgriculteur();

        $documentsStats = $documentRepository->getDashboardStatsForUtilisateur($currentUser);
        $documentsRecents = $documentRepository->findRecentByUtilisateur($currentUser, 5);

        $messagesNonLus = $messageRepository->countAllUnreadMessages($currentUser->getId());

        $compteStatut = [
            'status' => $agriculteur->getStatuscompte(),
            'verifie' => $agriculteur->isCompteverifie(),
        ];

        return $this->render('agriculteur/dashboard.html.twig', [
            'agriculteur' => $agriculteur,
            'compte_statut' => $compteStatut,
            'documents_stats' => $documentsStats,
            'messages_non_lus' => $messagesNonLus,
            'documents_recents' => $documentsRecents,
        ]);
    }

    #[Route('/produits', name: 'agriculteur_produits')]
    public function browseProducts(ProduitFinancierRepository $produitRepository): Response
    {
        return $this->render('agriculteur/produits.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/produits/{id}', name: 'agriculteur_produit_show')]
    public function showProduct(ProduitFinancier $produit): Response
    {
        return $this->render('agriculteur/produit_show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/offres', name: 'agriculteur_offres')]
    public function browseOffers(OffreFinanciereRepository $offreRepository): Response
    {
        return $this->render('agriculteur/offres.html.twig', [
            'offres' => $offreRepository->findActiveOffers(),
        ]);
    }

    #[Route('/simulateur', name: 'agriculteur_produit_simulateur', methods: ['GET', 'POST'])]
    public function simulateur(
        Request $request,
        ProduitFinancierRepository $repository,
        ProductService $productService
    ): Response {
        $produits = $repository->findAll();
        $simulation = null;
        $produitSelectionne = null;

        if ($request->isMethod('POST')) {
            $montant = (float) $request->request->get('montant', 0);
            $taux = (float) $request->request->get('taux', 0);
            $duree = (int) $request->request->get('duree', 12);
            $produitId = (int) $request->request->get('produit_id', 0);

            if ($produitId > 0) {
                $produitSelectionne = $repository->find($produitId);
                $taux = $produitSelectionne ? $produitSelectionne->getTauxInteret() : $taux;
            }

            $simulation = $productService->simulerCredit($montant, $taux, $duree);
        } elseif ($request->query->get('id')) {
            $produitSelectionne = $repository->find($request->query->get('id'));
        }

        return $this->render('agriculteur/simulateur.html.twig', [
            'produits' => $produits,
            'simulation' => $simulation,
            'produitSelectionne' => $produitSelectionne,
        ]);
    }

    #[Route('/simulateur-pdf', name: 'agriculteur_produit_simulateur_pdf', methods: ['POST'])]
    public function simulateurPdf(
        Request $request,
        ProduitFinancierRepository $repository,
        ProductService $productService,
        PdfService $pdfService
    ): Response {
        $montant = (float) $request->request->get('montant');
        $taux = (float) $request->request->get('taux');
        $duree = (int) $request->request->get('duree');
        $produitId = (int) $request->request->get('produit_id');
        $typeClient = $request->request->get('type_client', 'agriculture');

        $produit = $produitId > 0 ? $repository->find($produitId) : null;
        $simulation = $productService->simulerCredit($montant, $taux, $duree);

        return $pdfService->generatePdfResponse('pdf/simulateur.html.twig', [
            'simulation' => $simulation,
            'produit' => $produit,
            'typeClient' => $typeClient,
            'date' => new \DateTime(),
            'client_name' => $this->getUser()->getNomComplet(),
        ], 'simulation_agrifund.pdf');
    }

    #[Route('/offres/{id}/postuler', name: 'agriculteur_offre_postuler', methods: ['GET', 'POST'])]
    public function postulerOffre(
        int $id,
        Request $request,
        OffreFinanciereRepository $offreRepository,
        OfferCheckoutPreparationService $checkoutPreparationService,
        ApplicationNotificationService $applicationNotificationService,
        EntityManagerInterface $em
    ): Response {
        $offre = $offreRepository->find($id);
        if ($offre === null || $offre->getStatut() !== 'Active') {
            $this->addFlash('error', 'Offre introuvable.');

            return $this->redirectToRoute('agriculteur_offres');
        }

        if ($request->isMethod('GET')) {
            return $this->render('agriculteur/postuler_offre.html.twig', [
                'offre' => $offre,
                'invoiceEmail' => $this->getUser()?->getEmail() ?? '',
            ]);
        }

        if (!$this->isCsrfTokenValid('postuler_offre_' . $offre->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session invalide. Veuillez reessayer.');

            return $this->redirectToRoute('agriculteur_offre_postuler', ['id' => $offre->getId()]);
        }

        /** @var UploadedFile|null $proof */
        $proof = $request->files->get('payment_proof');
        if (!$proof instanceof UploadedFile) {
            $this->addFlash('error', 'Veuillez ajouter un justificatif avant de postuler.');

            return $this->redirectToRoute('agriculteur_offre_postuler', ['id' => $offre->getId()]);
        }

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];
        if (!in_array($proof->getMimeType(), $allowedMimeTypes, true)) {
            $this->addFlash('error', 'Formats autorises: PDF, JPEG, PNG, WEBP.');

            return $this->redirectToRoute('agriculteur_offre_postuler', ['id' => $offre->getId()]);
        }

        $proofSize = $proof->getSize();
        if ($proofSize !== null && $proofSize > 5 * 1024 * 1024) {
            $this->addFlash('error', 'Le justificatif depasse 5 Mo.');

            return $this->redirectToRoute('agriculteur_offre_postuler', ['id' => $offre->getId()]);
        }

        [$achat, $transaction] = $checkoutPreparationService->prepare(
            $this->getUser(),
            $offre,
            null,
            PaymentProvider::MANUAL,
            TransactionPaiement::METHOD_MANUAL
        );

        $invoiceEmail = trim((string) $request->request->get('invoice_email'));
        if ($invoiceEmail !== '' && filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL)) {
            $achat->setInvoiceEmail($invoiceEmail);
        }

        $uploadDir = (string) $this->getParameter('payment_proofs_directory');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = $proof->guessExtension() ?: $proof->getClientOriginalExtension() ?: 'bin';
        $filename = sprintf('offer_proof_%s_%s.%s', $offre->getId(), strtoupper(bin2hex(random_bytes(4))), strtolower($extension));
        $originalName = $proof->getClientOriginalName();
        $proof->move($uploadDir, $filename);

        $transaction->setGatewayPayload([
            'application_request' => true,
            'label' => 'Candidature offre avec justificatif',
            'offer_name' => $offre->getNomOffre(),
            'proof_path' => 'uploads/payment-proofs/' . $filename,
            'proof_original_name' => $originalName,
            'proof_size' => $proofSize,
            'proof_uploaded_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'review_status' => 'pending_admin_review',
            'submitted_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'notes' => trim((string) $request->request->get('application_note')),
        ]);
        $transaction->setVerificationStatus('pending');
        $transaction->setVerificationNote(null);
        $transaction->setVerifiedBy(null);
        $transaction->setVerifiedAt(null);

        $em->persist($achat);
        $em->persist($transaction);
        $em->flush();

        $applicationNotificationService->sendApplicationSubmittedEmails($transaction);

        $this->addFlash('success', 'Votre candidature a ete envoyee. Stay tuned: vous recevrez un email apres verification par l\'admin.');

        return $this->redirectToRoute('agriculteur_checkout_return', ['reference' => $achat->getReference()]);
    }

    #[Route('/produits/{id}/postuler', name: 'agriculteur_produit_postuler', methods: ['GET', 'POST'])]
    public function postulerProduit(
        int $id,
        Request $request,
        ProduitFinancierRepository $produitRepository,
        OfferCheckoutPreparationService $checkoutPreparationService,
        ApplicationNotificationService $applicationNotificationService,
        EntityManagerInterface $em
    ): Response {
        $produit = $produitRepository->find($id);
        if ($produit === null) {
            $this->addFlash('error', 'Produit introuvable.');

            return $this->redirectToRoute('agriculteur_produits');
        }

        if ($request->isMethod('GET')) {
            return $this->render('agriculteur/postuler_produit.html.twig', [
                'produit' => $produit,
                'invoiceEmail' => $this->getUser()?->getEmail() ?? '',
            ]);
        }

        if (!$this->isCsrfTokenValid('postuler_produit_' . $produit->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session invalide. Veuillez reessayer.');

            return $this->redirectToRoute('agriculteur_produit_postuler', ['id' => $produit->getId()]);
        }

        /** @var UploadedFile|null $proof */
        $proof = $request->files->get('payment_proof');
        if (!$proof instanceof UploadedFile) {
            $this->addFlash('error', 'Veuillez ajouter un justificatif avant de postuler.');

            return $this->redirectToRoute('agriculteur_produit_postuler', ['id' => $produit->getId()]);
        }

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];
        if (!in_array($proof->getMimeType(), $allowedMimeTypes, true)) {
            $this->addFlash('error', 'Formats autorises: PDF, JPEG, PNG, WEBP.');

            return $this->redirectToRoute('agriculteur_produit_postuler', ['id' => $produit->getId()]);
        }

        $proofSize = $proof->getSize();
        if ($proofSize !== null && $proofSize > 5 * 1024 * 1024) {
            $this->addFlash('error', 'Le justificatif depasse 5 Mo.');

            return $this->redirectToRoute('agriculteur_produit_postuler', ['id' => $produit->getId()]);
        }

        [$achat, $transaction] = $checkoutPreparationService->prepareProductForProvider(
            $this->getUser(),
            $produit,
            null,
            PaymentProvider::MANUAL,
            TransactionPaiement::METHOD_MANUAL
        );

        $invoiceEmail = trim((string) $request->request->get('invoice_email'));
        if ($invoiceEmail !== '' && filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL)) {
            $achat->setInvoiceEmail($invoiceEmail);
        }

        $uploadDir = (string) $this->getParameter('payment_proofs_directory');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = $proof->guessExtension() ?: $proof->getClientOriginalExtension() ?: 'bin';
        $filename = sprintf('product_proof_%s_%s.%s', $produit->getId(), strtoupper(bin2hex(random_bytes(4))), strtolower($extension));
        $originalName = $proof->getClientOriginalName();
        $proof->move($uploadDir, $filename);

        $transaction->setGatewayPayload([
            'application_request' => true,
            'label' => 'Candidature produit avec justificatif',
            'product_name' => $produit->getNomProduit(),
            'proof_path' => 'uploads/payment-proofs/' . $filename,
            'proof_original_name' => $originalName,
            'proof_size' => $proofSize,
            'proof_uploaded_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'review_status' => 'pending_admin_review',
            'submitted_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'notes' => trim((string) $request->request->get('application_note')),
        ]);
        $transaction->setVerificationStatus('pending');
        $transaction->setVerificationNote(null);
        $transaction->setVerifiedBy(null);
        $transaction->setVerifiedAt(null);

        $em->persist($achat);
        $em->persist($transaction);
        $em->flush();

        $applicationNotificationService->sendApplicationSubmittedEmails($transaction);

        $this->addFlash('success', 'Votre dossier produit a ete envoye. Stay tuned: vous recevrez un email apres verification par l\'admin.');

        return $this->redirectToRoute('agriculteur_checkout_return', ['reference' => $achat->getReference()]);
    }
}
