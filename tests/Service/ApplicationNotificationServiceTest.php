<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Achat;
use App\Entity\TransactionPaiement;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\Application\ApplicationNotificationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ApplicationNotificationServiceTest extends TestCase
{
    public function testSendApplicationEmailsSendMessages(): void
    {
        $client = (new Utilisateur())
            ->setNom('Client')
            ->setPrenom('Test')
            ->setEmail('client@example.com')
            ->setPassword('secret');

        $admin = (new Utilisateur())
            ->setNom('Admin')
            ->setPrenom('Root')
            ->setEmail('admin@example.com')
            ->setPassword('secret');

        $achat = (new Achat())
            ->setReference('ACH-TEST-001')
            ->setUtilisateur($client)
            ->setMontant('149.99')
            ->setDevise('TND')
            ->setInvoiceEmail('billing@example.com');

        $transaction = (new TransactionPaiement())
            ->setReference('PAY-TEST-001')
            ->setAchat($achat)
            ->setMontant('149.99')
            ->setDevise('TND');

        $repository = $this->createMock(UtilisateurRepository::class);
        $repository->expects(self::never())
            ->method('findFirstAdmin');

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::exactly(4))
            ->method('render')
            ->willReturnCallback(static function (string $template): string {
                return '<p>' . $template . '</p>';
            });

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::exactly(4))
            ->method('send')
            ->with(self::callback(static function ($message): bool {
                return $message instanceof Email;
            }));

        $service = new ApplicationNotificationService($mailer, $repository, $twig, 'AgriFund <onboarding@resend.dev>');
        $service->sendApplicationSubmittedEmails($transaction);
        $service->sendProofPendingApprovalEmail($transaction);
        $service->sendApplicationVerifiedEmails($transaction);
        $service->sendApplicationRejectedEmails($transaction);
    }
}
