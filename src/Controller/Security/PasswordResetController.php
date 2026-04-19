<?php

namespace App\Controller\Security;

use App\Entity\TokenReinitialisation;
use App\Entity\Utilisateur;
use App\Form\PasswordResetRequestType;
use App\Form\PasswordResetType;
use App\Repository\TokenReinitialisationRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class PasswordResetController extends AbstractController
{
    #[Route('/password-reset/request', name: 'app_password_reset_request')]
    public function request(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        TokenReinitialisationRepository $tokenRepository,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        #[Autowire('%env(MAILER_FROM_ADDRESS)%')] string $mailerFromAddress
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(PasswordResetRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $utilisateur = $utilisateurRepository->findOneByEmail($email);

            if ($utilisateur) {
                // Invalider les anciens tokens
                $tokenRepository->invalidateUserTokens($utilisateur);

                // Créer un nouveau token
                $token = new TokenReinitialisation();
                $token->setUtilisateur($utilisateur);
                $token->setToken(Uuid::v4()->toRfc4122());
                
                $expirationDate = new \DateTime();
                $expirationDate->modify('+1 hour');
                $token->setDateExpiration($expirationDate);

                $em->persist($token);
                $em->flush();

                // Envoyer l'email
                $resetUrl = $this->generateUrl('app_password_reset', [
                    'token' => $token->getToken()
                ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

                $emailMessage = (new Email())
                    ->from($mailerFromAddress)
                    ->to($utilisateur->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html($this->renderView('email/password_reset.html.twig', [
                        'utilisateur' => $utilisateur,
                        'resetUrl' => $resetUrl,
                        'expirationDate' => $expirationDate,
                    ]));

                try {
                    $mailer->send($emailMessage);
                    $this->addFlash('success', 'Un email de réinitialisation a été envoyé à votre adresse.');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.');
                }
            } else {
                // Pour des raisons de sécurité, on affiche le même message
                $this->addFlash('success', 'Si cette adresse email existe, vous recevrez un email de réinitialisation.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/password_reset_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/password-reset/{token}', name: 'app_password_reset')]
    public function reset(
        string $token,
        Request $request,
        TokenReinitialisationRepository $tokenRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $resetToken = $tokenRepository->findValidToken($token);

        if (!$resetToken) {
            $this->addFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
            return $this->redirectToRoute('app_password_reset_request');
        }

        $form = $this->createForm(PasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $resetToken->getUtilisateur();
            
            // Hash du nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $utilisateur,
                $form->get('plainPassword')->getData()
            );
            $utilisateur->setPassword($hashedPassword);

            // Marquer le token comme utilisé
            $resetToken->setUtilise(true);
            $resetToken->setDateUtilisation(new \DateTime());

            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/password_reset.html.twig', [
            'form' => $form->createView(),
            'token' => $token,
        ]);
    }
}
