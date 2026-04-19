<?php
// src/Service/TwoFactorAuthService.php

namespace App\Service;

use App\Entity\Code2fa;
use App\Entity\Parametres2fa;
use App\Entity\Utilisateur;
use App\Repository\Code2faRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class TwoFactorAuthService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private Code2faRepository $code2faRepository,
        private LoggerInterface $logger
    ) {}

    public function genererCode(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function creerEtEnvoyerCode(Utilisateur $utilisateur, ?string $methode = null): Code2fa
    {
        // Invalider les anciens codes
        $this->code2faRepository->invalidateOldCodes($utilisateur);

        // Déterminer la méthode d'envoi
        if ($methode === null) {
            $parametres = $utilisateur->getParametres2fa();
            $methode = $parametres ? $parametres->getMethodePreferee() : 'email';
        }

        // Créer un nouveau code
        $code2fa = new Code2fa();
        $code2fa->setUtilisateur($utilisateur);
        $code2fa->setCode($this->genererCode());
        $code2fa->setTypeEnvoi($methode);

        $this->em->persist($code2fa);
        $this->em->flush();

        // Envoyer le code
        try {
            if ($methode === 'email') {
                $this->envoyerCodeParEmail($utilisateur, $code2fa->getCode());
            } elseif ($methode === 'sms') {
                $this->envoyerCodeParSMS($utilisateur, $code2fa->getCode());
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur envoi code 2FA: ' . $e->getMessage());
            throw new \RuntimeException('Impossible d\'envoyer le code de vérification');
        }

        return $code2fa;
    }

    private function envoyerCodeParEmail(Utilisateur $utilisateur, string $code): void
    {
        $email = (new Email())
            ->from('noreply@agrifund.com')
            ->to($utilisateur->getEmail())
            ->subject('🔐 Votre code de vérification AgriFund')
            ->html($this->getEmailTemplate($code, $utilisateur->getNomComplet()));

        $this->mailer->send($email);
    }

    private function envoyerCodeParSMS(Utilisateur $utilisateur, string $code): void
    {
        // TODO: Implémenter l'envoi par SMS (Twilio, etc.)
        $this->logger->info("Code SMS pour {$utilisateur->getEmail()}: {$code}");
        
        // Pour le moment, on envoie aussi par email comme fallback
        $this->envoyerCodeParEmail($utilisateur, $code);
    }

    public function verifierCode(Utilisateur $utilisateur, string $code): bool
    {
        $code2fa = $this->code2faRepository->findValidCodeForUser($utilisateur, $code);

        if (!$code2fa) {
            return false;
        }

        // Marquer comme utilisé
        $code2fa->setEstUtilise(true);
        $code2fa->setDateUtilisation(new \DateTime());
        $this->em->flush();

        return true;
    }

    public function activerOuDesactiver2FA(
        Utilisateur $utilisateur, 
        bool $activer, 
        ?string $telephone = null,
        ?string $methode = null
    ): void {
        $parametres = $utilisateur->getParametres2fa();
        
        if (!$parametres) {
            $parametres = new Parametres2fa();
            $parametres->setUtilisateur($utilisateur);
            $utilisateur->setParametres2fa($parametres);
        }

        $parametres->setEstActive($activer);
        
        if ($activer) {
            $parametres->setDateActivation(new \DateTime());
            if ($telephone) {
                $parametres->setTelephone2fa($telephone);
            }
            if ($methode) {
                $parametres->setMethodePreferee($methode);
            }
        }

        $this->em->persist($parametres);
        $this->em->flush();
    }

    private function getEmailTemplate(string $code, string $nom): string
    {
        return "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8faf5 0%, #e8f5e9 100%);
            padding: 40px 20px;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        .header { 
            background: linear-gradient(135deg, #476C1A 0%, #095032 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='30' cy='30' r='15' fill='none' stroke='%23B2D944' stroke-opacity='0.1' stroke-width='2'/%3E%3C/svg%3E\");
            opacity: 0.3;
        }
        .header h1 { 
            color: white;
            font-size: 28px;
            margin-bottom: 10px;
            position: relative;
        }
        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            position: relative;
        }
        .content { 
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #133D03;
            margin-bottom: 20px;
        }
        .message {
            color: #476C1A;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .code-box { 
            background: linear-gradient(135deg, #f8faf5 0%, #e8f5e9 100%);
            border: 3px dashed #089647;
            padding: 30px;
            text-align: center;
            border-radius: 15px;
            margin: 30px 0;
            position: relative;
        }
        .code-box::before {
            content: '🔐';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .code-label {
            font-size: 14px;
            color: #476C1A;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .code { 
            font-size: 48px;
            font-weight: bold;
            color: #089647;
            letter-spacing: 10px;
            font-family: 'Courier New', monospace;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        .expiry {
            background: linear-gradient(135deg, #E1B323 0%, #c9a020 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            margin: 25px 0;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(225, 179, 35, 0.3);
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #E1B323;
            padding: 15px 20px;
            border-radius: 8px;
            color: #856404;
            margin-top: 25px;
        }
        .footer { 
            background: #f8faf5;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e8f5e9;
        }
        .footer-text {
            color: #848A86;
            font-size: 13px;
            line-height: 1.6;
        }
        .logo {
            color: #089647;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .social-links {
            margin-top: 20px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #089647;
            text-decoration: none;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🌾 AgriFund</h1>
            <p>Authentification sécurisée</p>
        </div>
        
        <div class='content'>
            <div class='greeting'>
                Bonjour <strong>{$nom}</strong>,
            </div>
            
            <div class='message'>
                <p>Vous avez demandé à vous connecter à votre compte AgriFund. Pour des raisons de sécurité, veuillez utiliser le code de vérification ci-dessous :</p>
            </div>
            
            <div class='code-box'>
                <div class='code-label'>Votre code de vérification</div>
                <div class='code'>{$code}</div>
            </div>
            
            <div class='expiry'>
                ⏱️ Ce code expire dans <strong>5 minutes</strong>
            </div>
            
            <div class='warning'>
                <strong>⚠️ Important :</strong><br>
                Si vous n'avez pas demandé ce code, veuillez ignorer cet email et sécuriser votre compte immédiatement.
            </div>
        </div>
        
        <div class='footer'>
            <div class='logo'>AgriFund</div>
            <div class='footer-text'>
                <p>Plateforme de financement agricole</p>
                <p style='margin-top: 10px;'>© " . date('Y') . " AgriFund - Tous droits réservés</p>
            </div>
        </div>
    </div>
</body>
</html>
        ";
    }
}