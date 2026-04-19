<?php
// src/Service/EmailVerificationService.php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Entity\VerificationToken;
use App\Repository\VerificationTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailVerificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $em,
        private VerificationTokenRepository $tokenRepository,
        #[Autowire('%env(MAILER_FROM_ADDRESS)%')]
        private string $mailerFromAddress
    ) {}

    public function sendVerificationEmail(Utilisateur $utilisateur): void
    {
        // Supprimer les anciens tokens pour cet utilisateur
        $oldTokens = $this->tokenRepository->findBy(['utilisateur' => $utilisateur]);
        foreach ($oldTokens as $oldToken) {
            $this->em->remove($oldToken);
        }
        $this->em->flush();

        // Générer un nouveau token unique
        $token = bin2hex(random_bytes(32));
        
        // Créer et persister le token
        $verificationToken = new VerificationToken();
        $verificationToken->setToken($token);
        $verificationToken->setUtilisateur($utilisateur);
        
        $this->em->persist($verificationToken);
        $this->em->flush();

        // Générer l'URL de vérification
        $verificationUrl = $this->urlGenerator->generate(
            'app_verify_email',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Déterminer le type d'utilisateur
        $userType = $utilisateur->getAgriculteur() ? 'Agriculteur' : 'Banque';

        // Créer et envoyer l'email
        $email = (new TemplatedEmail())
            ->from($this->mailerFromAddress)
            ->to($utilisateur->getEmail())
            ->subject('Vérifiez votre adresse email - AgriFund')
            ->htmlTemplate('emails/verification.html.twig')
            ->context([
                'utilisateur' => $utilisateur,
                'verificationUrl' => $verificationUrl,
                'userType' => $userType,
                'expiresAt' => $verificationToken->getExpiresAt(),
            ]);

        $this->mailer->send($email);
    }

    public function verifyToken(string $token): ?Utilisateur
    {
        // Rechercher le token valide
        $verificationToken = $this->tokenRepository->findValidToken($token);

        if (!$verificationToken) {
            return null;
        }

        // Vérifier manuellement si le token n'est pas expiré (sécurité supplémentaire)
        if ($verificationToken->isExpired()) {
            return null;
        }

        // Récupérer l'utilisateur et le marquer comme vérifié
        $utilisateur = $verificationToken->getUtilisateur();
        $utilisateur->setIsVerified(true);

        // Supprimer le token utilisé
        $this->em->remove($verificationToken);
        $this->em->flush();

        return $utilisateur;
    }

    public function resendVerificationEmail(Utilisateur $utilisateur): void
    {
        if ($utilisateur->isVerified()) {
            throw new \LogicException('Cet utilisateur est déjà vérifié.');
        }

        $this->sendVerificationEmail($utilisateur);
    }
}