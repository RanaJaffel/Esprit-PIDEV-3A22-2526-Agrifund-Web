<?php

declare(strict_types=1);

namespace App\Controller\Agriculteur;

use App\Entity\Achat;
use App\Entity\TransactionPaiement;
use App\Entity\Utilisateur;
use App\Payment\AchatStatus;
use App\Payment\PaymentProvider;
use App\Payment\PaymentStatus;
use App\Repository\AchatRepository;
use App\Repository\OffreFinanciereRepository;
use App\Repository\ProduitFinancierRepository;
use App\Service\Application\ApplicationNotificationService;
use App\Service\Payment\OfferCheckoutPreparationService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur')]
#[IsGranted('ROLE_AGRICULTEUR')]
class CheckoutController extends AbstractController
{
    #[Route('/offres/{id}/checkout', name: 'agriculteur_offre_checkout', methods: ['GET'])]
    public function showOfferCheckout(
        int $id,
        OffreFinanciereRepository $offreRepository,
        OfferCheckoutPreparationService $checkoutPreparationService
    ): Response {
        return $this->redirectToRoute('agriculteur_offre_postuler', ['id' => $id]);
    }

    #[Route('/offres/{id}/checkout/start', name: 'agriculteur_offre_checkout_start', methods: ['POST'])]
    public function startOfferCheckout(
        int $id,
        Request $request,
        OffreFinanciereRepository $offreRepository,
        OfferCheckoutPreparationService $checkoutPreparationService,
        EntityManagerInterface $entityManager
    ): Response {
        return $this->redirectToRoute('agriculteur_offre_postuler', ['id' => $id]);
    }

    #[Route('/produits/{id}/checkout', name: 'agriculteur_produit_checkout', methods: ['GET'])]
    public function showProductCheckout(
        int $id,
        ProduitFinancierRepository $produitRepository,
        OfferCheckoutPreparationService $checkoutPreparationService
    ): Response {
        return $this->redirectToRoute('agriculteur_produit_postuler', ['id' => $id]);
    }

    #[Route('/produits/{id}/checkout/start', name: 'agriculteur_produit_checkout_start', methods: ['POST'])]
    public function startProductCheckout(
        int $id,
        Request $request,
        ProduitFinancierRepository $produitRepository,
        OfferCheckoutPreparationService $checkoutPreparationService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        return $this->redirectToRoute('agriculteur_produit_postuler', ['id' => $id]);
    }

    #[Route('/checkout/{reference}/return', name: 'agriculteur_checkout_return', methods: ['GET'])]
    public function checkoutReturn(string $reference, AchatRepository $achatRepository): Response
    {
        $utilisateur = $this->requireUtilisateur();
        $achat = $achatRepository->findOneByReference($reference);

        if ($achat === null || $achat->getUtilisateur()?->getId() !== $utilisateur->getId()) {
            throw $this->createNotFoundException('Achat introuvable.');
        }

        return $this->render('agriculteur/checkout_return.html.twig', [
            'achat' => $achat,
            'reference' => $reference,
            'isPaid' => $achat->getStatut() === AchatStatus::PAID,
        ]);
    }

    #[Route('/checkout/{reference}/manual-proof', name: 'agriculteur_checkout_manual_proof_upload', methods: ['POST'])]
    public function uploadManualProof(
        string $reference,
        Request $request,
        AchatRepository $achatRepository,
        EntityManagerInterface $entityManager,
        ApplicationNotificationService $applicationNotificationService
    ): Response {
        $utilisateur = $this->requireUtilisateur();
        $achat = $achatRepository->findOneByReference($reference);

        if ($achat === null || $achat->getUtilisateur()?->getId() !== $utilisateur->getId()) {
            throw $this->createNotFoundException('Achat introuvable.');
        }

        if (!$this->isCsrfTokenValid('manual_proof_' . $achat->getReference(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $transaction = $this->findLatestTransaction($achat);
        if ($transaction === null || $transaction->getProvider() !== PaymentProvider::MANUAL) {
            $this->addFlash('error', 'Aucun paiement local a mettre a jour.');

            return $this->redirectToRoute('agriculteur_checkout_return', ['reference' => $reference]);
        }

        /** @var UploadedFile|null $proof */
        $proof = $request->files->get('payment_proof');
        if (!$proof instanceof UploadedFile) {
            $this->addFlash('error', 'Veuillez selectionner un justificatif de paiement.');

            return $this->redirectToRoute('agriculteur_checkout_return', ['reference' => $reference]);
        }

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (!in_array($proof->getMimeType(), $allowedMimeTypes, true)) {
            $this->addFlash('error', 'Formats autorises: PDF, JPEG, PNG, WEBP.');

            return $this->redirectToRoute('agriculteur_checkout_return', ['reference' => $reference]);
        }

        $proofSize = $proof->getSize();
        if ($proofSize !== null && $proofSize > 5 * 1024 * 1024) {
            $this->addFlash('error', 'Le fichier est trop volumineux. Taille maximale: 5 Mo.');

            return $this->redirectToRoute('agriculteur_checkout_return', ['reference' => $reference]);
        }

        $uploadDir = (string) $this->getParameter('payment_proofs_directory');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = $proof->guessExtension() ?: $proof->getClientOriginalExtension() ?: 'bin';
        $filename = sprintf('proof_%s_%s.%s', $achat->getReference(), strtoupper(bin2hex(random_bytes(4))), strtolower($extension));
        $originalName = $proof->getClientOriginalName();
        $proof->move($uploadDir, $filename);

        $payload = $transaction->getGatewayPayload() ?? [];
        $payload['proof_path'] = 'uploads/payment-proofs/' . $filename;
        $payload['proof_original_name'] = $originalName;
        $payload['proof_size'] = $proofSize;
        $payload['proof_uploaded_at'] = (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);
        $payload['review_status'] = 'pending_admin_review';
        $transaction->setGatewayPayload($payload);
        $transaction->setStatut(PaymentStatus::PENDING);
        $transaction->setVerificationStatus('pending');
        $transaction->setVerificationNote(null);
        $transaction->setVerifiedBy(null);
        $transaction->setVerifiedAt(null);
        $achat->setStatut(AchatStatus::PENDING_GATEWAY);

        $entityManager->flush();

        $applicationNotificationService->sendProofPendingApprovalEmail($transaction);

        $this->addFlash('success', 'Le justificatif de paiement a ete televerse et sera examine par un administrateur.');

        return $this->redirectToRoute('agriculteur_checkout_return', ['reference' => $reference]);
    }

    #[Route('/checkout/{reference}/receipt', name: 'agriculteur_checkout_receipt', methods: ['GET'])]
    public function downloadReceipt(
        string $reference,
        AchatRepository $achatRepository,
        PdfService $pdfService
    ): Response {
        $utilisateur = $this->requireUtilisateur();
        $achat = $achatRepository->findOneByReference($reference);

        if ($achat === null || $achat->getUtilisateur()?->getId() !== $utilisateur->getId()) {
            throw $this->createNotFoundException('Achat introuvable.');
        }

        $this->addFlash('info', 'Le dossier est consultable depuis la page de suivi.');

        return $this->redirectToRoute('agriculteur_checkout_return', ['reference' => $reference]);
    }

    private function requireUtilisateur(): Utilisateur
    {
        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Utilisateur non autorise.');
        }

        return $user;
    }

    private function resolveInvoiceEmail(?string $submittedEmail, Utilisateur $utilisateur): ?string
    {
        $email = trim((string) $submittedEmail);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        $userEmail = $utilisateur->getEmail();
        if ($userEmail !== null && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            return $userEmail;
        }

        return null;
    }

    private function normalizeCheckoutMethod(mixed $method): string
    {
        $normalized = strtolower(trim((string) $method));

        return $normalized === TransactionPaiement::METHOD_MANUAL
            ? TransactionPaiement::METHOD_MANUAL
            : TransactionPaiement::METHOD_CARD;
    }

    private function validateCardSubmission(Request $request): ?string
    {
        $holder = trim((string) $request->request->get('card_holder'));
        $number = preg_replace('/\D+/', '', (string) $request->request->get('card_number'));
        $expiry = trim((string) $request->request->get('card_expiry'));

        if ($holder === '' || $number === '' || $expiry === '') {
            return 'Veuillez renseigner le titulaire, le numero et la date d\'expiration de la carte.';
        }

        if (strlen($number) < 12 || strlen($number) > 19) {
            return 'Le numero de carte saisi semble invalide.';
        }

        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
            return 'La date d\'expiration doit respecter le format MM/AA.';
        }

        return null;
    }

    private function buildLocalPaymentPayload(TransactionPaiement $transaction, Request $request, string $paymentMethod): array
    {
        if ($paymentMethod === TransactionPaiement::METHOD_CARD) {
            $cardNumber = preg_replace('/\D+/', '', (string) $request->request->get('card_number'));
            $cardBrand = $this->detectCardBrand($cardNumber);

            return [
                'card_payment' => true,
                'label' => 'Carte bancaire - verification manuelle',
                'card_brand' => $cardBrand,
                'card_holder' => trim((string) $request->request->get('card_holder')),
                'card_last4' => substr($cardNumber, -4),
                'card_expiry' => trim((string) $request->request->get('card_expiry')),
                'review_status' => 'pending_admin_review',
                'submitted_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ];
        }

        return $this->buildManualPaymentPayload($transaction);
    }

    private function detectCardBrand(string $digits): string
    {
        if (preg_match('/^4/', $digits) === 1) {
            return 'VISA';
        }

        if (preg_match('/^(5[1-5]|2[2-7])/', $digits) === 1) {
            return 'MASTERCARD';
        }

        if (preg_match('/^3[47]/', $digits) === 1) {
            return 'AMEX';
        }

        if (preg_match('/^6(?:011|5)/', $digits) === 1) {
            return 'DISCOVER';
        }

        return 'CARTE';
    }

    private function buildManualPaymentPayload(TransactionPaiement $transaction): array
    {
        $referencePrefix = trim((string) ($_ENV['MANUAL_PAYMENT_REFERENCE_PREFIX'] ?? 'AGF'));

        return [
            'manual_payment' => true,
            'label' => (string) ($_ENV['MANUAL_PAYMENT_LABEL'] ?? 'Virement bancaire / Paiement au bureau'),
            'account_name' => (string) ($_ENV['MANUAL_PAYMENT_ACCOUNT_NAME'] ?? 'AgriFund'),
            'bank_name' => (string) ($_ENV['MANUAL_PAYMENT_BANK_NAME'] ?? 'Banque AgriFund'),
            'iban' => (string) ($_ENV['MANUAL_PAYMENT_IBAN'] ?? ''),
            'instructions' => (string) ($_ENV['MANUAL_PAYMENT_INSTRUCTIONS'] ?? ''),
            'payment_reference' => sprintf('%s-%s', $referencePrefix !== '' ? $referencePrefix : 'AGF', $transaction->getReference()),
            'review_status' => 'pending_admin_review',
            'submitted_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];
    }

    private function findLatestSuccessfulTransaction(Achat $achat): ?TransactionPaiement
    {
        $latest = null;
        foreach ($achat->getTransactions() as $transaction) {
            if ($transaction->getStatut() !== PaymentStatus::SUCCEEDED) {
                continue;
            }

            if ($latest === null || ($transaction->getProcessedAt()?->getTimestamp() ?? 0) > ($latest->getProcessedAt()?->getTimestamp() ?? 0)) {
                $latest = $transaction;
            }
        }

        return $latest;
    }

    private function findLatestTransaction(Achat $achat): ?TransactionPaiement
    {
        $latest = null;

        foreach ($achat->getTransactions() as $transaction) {
            if ($latest === null || ($transaction->getCreatedAt()?->getTimestamp() ?? 0) > ($latest->getCreatedAt()?->getTimestamp() ?? 0)) {
                $latest = $transaction;
            }
        }

        return $latest;
    }
}
