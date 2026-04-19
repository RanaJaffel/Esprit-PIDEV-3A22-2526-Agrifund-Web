<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\TransactionPaiement;
use App\Payment\AchatStatus;
use App\Payment\PaymentProvider;
use App\Payment\PaymentStatus;
use App\Repository\TransactionPaiementRepository;
use App\Service\Application\ApplicationNotificationService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/paiements')]
#[IsGranted('ROLE_ADMIN')]
class AdminPaymentController extends AbstractController
{
    #[Route('', name: 'admin_payment_index', methods: ['GET'])]
    public function index(
        Request $request,
        TransactionPaiementRepository $transactionRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $status = $request->query->getString('status', '');
        $method = $request->query->getString('method', '');
        $provider = $request->query->getString('provider', '');

        $queryBuilder = $transactionRepository
            ->createAdminSearchQueryBuilder(
                $status !== '' ? $status : null,
                $method !== '' ? $method : null,
                $provider !== '' ? $provider : null
            );

        $transactions = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/paiement/index.html.twig', [
            'transactions' => $transactions,
            'selectedStatus' => $status,
            'selectedMethod' => $method,
            'selectedProvider' => $provider,
            'availableStatuses' => PaymentStatus::all(),
            'availableMethods' => [
                TransactionPaiement::METHOD_CARD,
                TransactionPaiement::METHOD_MANUAL,
            ],
            'availableProviders' => [
                PaymentProvider::MANUAL,
            ],
        ]);
    }

    #[Route('/{reference}/approve', name: 'admin_payment_approve', methods: ['POST'])]
    public function approve(
        string $reference,
        Request $request,
        TransactionPaiementRepository $transactionRepository,
        EntityManagerInterface $entityManager,
        ApplicationNotificationService $applicationNotificationService,
        LoggerInterface $logger
    ): Response {
        $transaction = $transactionRepository->findOneByReference($reference);
        if ($transaction === null) {
            throw $this->createNotFoundException('Transaction introuvable.');
        }

        if (!$this->isCsrfTokenValid('approve_payment_' . $transaction->getReference(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $approvedAmount = (float) $request->request->get('approved_amount', 0);
        $approvedAtInput = trim((string) $request->request->get('approved_at', ''));
        $verifiedAtInput = trim((string) $request->request->get('verified_at', ''));
        $verifiedByInput = trim((string) $request->request->get('verified_by', ''));
        $reviewNote = trim((string) $request->request->get('review_note', ''));

        if ($approvedAmount <= 0) {
            $this->addFlash('error', 'Le montant approuve doit etre superieur a 0.');

            return $this->redirectToRoute('admin_payment_index');
        }

        if ($approvedAtInput === '') {
            $this->addFlash('error', 'La date de validation est obligatoire.');

            return $this->redirectToRoute('admin_payment_index');
        }

        if ($reviewNote === '') {
            $this->addFlash('error', 'Un commentaire de validation est obligatoire.');

            return $this->redirectToRoute('admin_payment_index');
        }

        try {
            $approvedAt = new \DateTimeImmutable($approvedAtInput);
        } catch (\Throwable) {
            $this->addFlash('error', 'Format de date invalide pour la validation.');

            return $this->redirectToRoute('admin_payment_index');
        }

        $verifiedAt = $approvedAt;
        if ($verifiedAtInput !== '') {
            try {
                $verifiedAt = new \DateTimeImmutable($verifiedAtInput);
            } catch (\Throwable) {
                $this->addFlash('error', 'Format de date invalide pour la verification.');

                return $this->redirectToRoute('admin_payment_index');
            }
        }

        $verifiedBy = $verifiedByInput !== '' ? $verifiedByInput : $this->getUser()?->getUserIdentifier();

        $transaction->setStatut(PaymentStatus::SUCCEEDED);
        $transaction->setProcessedAt($approvedAt);
        $transaction->setMontant(number_format($approvedAmount, 2, '.', ''));
        $transaction->setVerificationStatus('approved');
        $transaction->setVerificationNote($reviewNote);
        $transaction->setVerifiedBy($verifiedBy);
        $transaction->setVerifiedAt($verifiedAt);

        $payload = $transaction->getGatewayPayload() ?? [];
        $payload['review_status'] = 'approved';
        $payload['reviewed_at'] = $approvedAt->format(\DateTimeInterface::ATOM);
        $payload['reviewed_by'] = $verifiedBy;
        $payload['approved_amount'] = number_format($approvedAmount, 2, '.', '');
        $payload['review_note'] = $reviewNote;
        $transaction->setGatewayPayload($payload);

        $achat = $transaction->getAchat();
        $achat->setStatut(AchatStatus::PAID);
        $achat->setPaidAt($approvedAt);

        $entityManager->flush();

        try {
            $applicationNotificationService->sendApplicationVerifiedEmails($transaction);
        } catch (\Throwable $exception) {
            $logger->error('Application approval email dispatch failed.', [
                'transaction_reference' => $transaction->getReference(),
                'exception' => $exception->getMessage(),
            ]);
        }

        $this->addFlash('success', 'Le justificatif a ete valide.');

        return $this->redirectToRoute('admin_payment_index');
    }

    #[Route('/{reference}/reject', name: 'admin_payment_reject', methods: ['POST'])]
    public function reject(
        string $reference,
        Request $request,
        TransactionPaiementRepository $transactionRepository,
        EntityManagerInterface $entityManager,
        ApplicationNotificationService $applicationNotificationService,
        LoggerInterface $logger
    ): Response {
        $transaction = $transactionRepository->findOneByReference($reference);
        if ($transaction === null) {
            throw $this->createNotFoundException('Transaction introuvable.');
        }

        if (!$this->isCsrfTokenValid('reject_payment_' . $transaction->getReference(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $rejectionReason = trim((string) $request->request->get('rejection_reason', ''));
        if ($rejectionReason === '') {
            $this->addFlash('error', 'Le motif de rejet est obligatoire.');

            return $this->redirectToRoute('admin_payment_index');
        }

        $transaction->setStatut(PaymentStatus::FAILED);
        $reviewedAt = new \DateTimeImmutable();
        $transaction->setProcessedAt($reviewedAt);
        $transaction->setVerificationStatus('rejected');
        $transaction->setVerificationNote($rejectionReason);
        $transaction->setVerifiedBy($this->getUser()?->getUserIdentifier());
        $transaction->setVerifiedAt($reviewedAt);

        $payload = $transaction->getGatewayPayload() ?? [];
        $payload['review_status'] = 'rejected';
        $payload['reviewed_at'] = $reviewedAt->format(\DateTimeInterface::ATOM);
        $payload['reviewed_by'] = $this->getUser()?->getUserIdentifier();
        $payload['rejection_reason'] = $rejectionReason;
        $transaction->setGatewayPayload($payload);

        $transaction->getAchat()->setStatut(AchatStatus::FAILED);
        $entityManager->flush();

        try {
            $applicationNotificationService->sendApplicationRejectedEmails($transaction);
        } catch (\Throwable $exception) {
            $logger->error('Application rejection email dispatch failed.', [
                'transaction_reference' => $transaction->getReference(),
                'exception' => $exception->getMessage(),
            ]);
        }

        $this->addFlash('warning', 'Le paiement a ete rejete.');

        return $this->redirectToRoute('admin_payment_index');
    }

    #[Route('/{reference}/delete', name: 'admin_payment_delete', methods: ['POST'])]
    public function delete(
        string $reference,
        Request $request,
        TransactionPaiementRepository $transactionRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        $transaction = $transactionRepository->findOneByReference($reference);
        if ($transaction === null) {
            throw $this->createNotFoundException('Transaction introuvable.');
        }

        if (!$this->isCsrfTokenValid('delete_payment_' . $transaction->getReference(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $payload = $transaction->getGatewayPayload() ?? [];
        $proofPath = (string) ($payload['proof_path'] ?? '');

        if ($proofPath !== '') {
            $absolutePath = (string) $this->getParameter('kernel.project_dir') . '/public/' . ltrim($proofPath, '/');
            if (is_file($absolutePath)) {
                try {
                    @unlink($absolutePath);
                } catch (\Throwable $exception) {
                    $logger->warning('Failed to delete payment proof file.', [
                        'transaction_reference' => $transaction->getReference(),
                        'proof_path' => $absolutePath,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            }
        }

        $entityManager->remove($transaction);
        $entityManager->flush();

        $this->addFlash('success', 'Le paiement a ete supprime.');

        return $this->redirectToRoute('admin_payment_index');
    }

    #[Route('/{reference}/download-pdf', name: 'admin_payment_download_pdf', methods: ['GET'])]
    public function downloadPdf(
        string $reference,
        TransactionPaiementRepository $transactionRepository,
        PdfService $pdfService
    ): Response {
        $transaction = $transactionRepository->findOneByReference($reference);
        if ($transaction === null) {
            throw $this->createNotFoundException('Transaction introuvable.');
        }

        return $pdfService->generatePdfResponse(
            'pdf/paiement_transaction.html.twig',
            [
                'transaction' => $transaction,
                'achat' => $transaction->getAchat(),
                'payload' => $transaction->getGatewayPayload() ?? [],
                'generatedAt' => new \DateTimeImmutable(),
            ],
            sprintf('paiement-%s.pdf', $transaction->getReference())
        );
    }
}
