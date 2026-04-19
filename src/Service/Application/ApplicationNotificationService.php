<?php

declare(strict_types=1);

namespace App\Service\Application;

use App\Entity\TransactionPaiement;
use App\Repository\UtilisateurRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ApplicationNotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly Environment $twig,
        #[Autowire('%env(MAILER_FROM_ADDRESS)%')]
        private readonly string $fromAddress
    ) {
    }

    public function sendApplicationSubmittedEmails(TransactionPaiement $transaction): void
    {
        $achat = $transaction->getAchat();
        $utilisateur = $achat->getUtilisateur();
        $recipientEmail = $achat->getInvoiceEmail() ?? $utilisateur->getEmail();

        if ($recipientEmail !== null) {
            $email = (new Email())
                ->from($this->fromAddress)
                ->to($recipientEmail)
                ->subject('Dossier recu - Stay tuned')
                ->html($this->twig->render('email/application_submitted_customer.html.twig', [
                    'utilisateur' => $utilisateur,
                    'achat' => $achat,
                    'transaction' => $transaction,
                ]));

            $this->mailer->send($email);
        }
    }

    public function sendProofPendingApprovalEmail(TransactionPaiement $transaction): void
    {
        $achat = $transaction->getAchat();
        $utilisateur = $achat->getUtilisateur();
        $recipientEmail = $achat->getInvoiceEmail() ?? $utilisateur->getEmail();

        if ($recipientEmail !== null) {
            $email = (new Email())
                ->from($this->fromAddress)
                ->to($recipientEmail)
                ->subject('Justificatif recu - En attente de validation admin')
                ->html($this->twig->render('email/proof_pending_admin_customer.html.twig', [
                    'utilisateur' => $utilisateur,
                    'achat' => $achat,
                    'transaction' => $transaction,
                ]));

            $this->mailer->send($email);
        }
    }

    public function sendApplicationVerifiedEmails(TransactionPaiement $transaction): void
    {
        $achat = $transaction->getAchat();
        $utilisateur = $achat->getUtilisateur();
        $recipientEmail = $achat->getInvoiceEmail() ?? $utilisateur->getEmail();

        if ($recipientEmail !== null) {
            $email = (new Email())
                ->from($this->fromAddress)
                ->to($recipientEmail)
                ->subject('Justificatif verifie - AgriFund')
                ->html($this->twig->render('email/application_verified_customer.html.twig', [
                    'utilisateur' => $utilisateur,
                    'achat' => $achat,
                    'transaction' => $transaction,
                ]));

            $this->mailer->send($email);
        }
    }

    public function sendApplicationRejectedEmails(TransactionPaiement $transaction): void
    {
        $achat = $transaction->getAchat();
        $utilisateur = $achat->getUtilisateur();
        $recipientEmail = $achat->getInvoiceEmail() ?? $utilisateur->getEmail();

        if ($recipientEmail !== null) {
            $email = (new Email())
                ->from($this->fromAddress)
                ->to($recipientEmail)
                ->subject('Justificatif refuse - AgriFund')
                ->html($this->twig->render('email/application_rejected_customer.html.twig', [
                    'utilisateur' => $utilisateur,
                    'achat' => $achat,
                    'transaction' => $transaction,
                ]));

            $this->mailer->send($email);
        }
    }
}
