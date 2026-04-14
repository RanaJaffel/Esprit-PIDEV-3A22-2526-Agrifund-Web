<?php

namespace App\Service;

use App\Entity\Agriculteur;
use App\Entity\Banque;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private string $fromEmail;

    public function __construct(
        MailerInterface $mailer, 
        Environment $twig, 
        string $fromEmail = 'noreply@agrifund.com'
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->fromEmail = $fromEmail;
    }

    /**
     * Envoie un email d'approbation à un agriculteur
     */
    public function sendAgriculteurApprovalEmail(Agriculteur $agriculteur): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($agriculteur->getUtilisateur()->getEmail())
            ->subject('✅ Votre compte AgriFund a été approuvé')
            ->html($this->twig->render('emails/agriculteur_approved.html.twig', [
                'agriculteur' => $agriculteur
            ]));

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de rejet à un agriculteur
     */
    public function sendAgriculteurRejectionEmail(Agriculteur $agriculteur): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($agriculteur->getUtilisateur()->getEmail())
            ->subject('Votre demande de compte AgriFund')
            ->html($this->twig->render('emails/agriculteur_rejected.html.twig', [
                'agriculteur' => $agriculteur
            ]));

        $this->mailer->send($email);
    }

    /**
     * Envoie un email d'approbation à une banque
     */
    public function sendBanqueApprovalEmail(Banque $banque): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($banque->getUtilisateur()->getEmail())
            ->subject('✅ Votre compte bancaire AgriFund a été approuvé')
            ->html($this->twig->render('emails/banque_approved.html.twig', [
                'banque' => $banque
            ]));

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de rejet à une banque
     */
    public function sendBanqueRejectionEmail(Banque $banque): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($banque->getUtilisateur()->getEmail())
            ->subject('Votre demande de compte bancaire AgriFund')
            ->html($this->twig->render('emails/banque_rejected.html.twig', [
                'banque' => $banque
            ]));

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de suspension à un agriculteur
     */
    public function sendAgriculteurSuspensionEmail(Agriculteur $agriculteur): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($agriculteur->getUtilisateur()->getEmail())
            ->subject('⚠️ Votre compte AgriFund a été suspendu')
            ->html($this->twig->render('emails/agriculteur_suspended.html.twig', [
                'agriculteur' => $agriculteur
            ]));

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de suspension à une banque
     */
    public function sendBanqueSuspensionEmail(Banque $banque): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($banque->getUtilisateur()->getEmail())
            ->subject('⚠️ Votre compte bancaire AgriFund a été suspendu')
            ->html($this->twig->render('emails/banque_suspended.html.twig', [
                'banque' => $banque
            ]));

        $this->mailer->send($email);
    }
}