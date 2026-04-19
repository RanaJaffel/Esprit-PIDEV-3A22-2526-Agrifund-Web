<?php

namespace App\Controller\Security;

use App\Entity\TokenReinitialisation;
use App\Form\PasswordResetRequestType;
use App\Form\PasswordResetType;
use App\Repository\TokenReinitialisationRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        LoggerInterface $logger
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
                try {
                    $tokenRepository->invalidateUserTokens($utilisateur);

                    $token = new TokenReinitialisation();
                    $token->setUtilisateur($utilisateur);
                    $token->setToken(Uuid::v4()->toRfc4122());

                    $expirationDate = new \DateTime();
                    $expirationDate->modify('+1 hour');
                    $token->setDateExpiration($expirationDate);

                    $em->persist($token);
                    $em->flush();

                    $resetUrl = $this->generateUrl('app_password_reset', [
                        'token' => $token->getToken(),
                    ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

                    $emailMessage = (new Email())
                        ->from('souleimab945@gmail.com')
                        ->to($utilisateur->getEmail())
                        ->subject('Reinitialisation de votre mot de passe')
                        ->html($this->renderView('email/password_reset.html.twig', [
                            'utilisateur' => $utilisateur,
                            'resetUrl' => $resetUrl,
                            'expirationDate' => $expirationDate,
                        ]));

                    $mailer->send($emailMessage);

                    $logger->info('Email de reinitialisation envoye', [
                        'email' => $utilisateur->getEmail(),
                        'token' => $token->getToken(),
                    ]);

                    $this->addFlash('success', 'Un email de reinitialisation a ete envoye a votre adresse.');
                } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                    $logger->error('Erreur Transport Email', [
                        'message' => $e->getMessage(),
                        'email' => $email,
                    ]);
                    $this->addFlash('error', 'Erreur de connexion au serveur email : ' . $e->getMessage());
                } catch (\Exception $e) {
                    $logger->error('Erreur envoi email', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->addFlash('error', 'Erreur technique : ' . $e->getMessage());
                }
            } else {
                $this->addFlash('success', 'Si cette adresse email existe, vous recevrez un email de reinitialisation.');
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
            $this->addFlash('error', 'Ce lien de reinitialisation est invalide ou a expire.');
            return $this->redirectToRoute('app_password_reset_request');
        }

        $form = $this->createForm(PasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $resetToken->getUtilisateur();

            $hashedPassword = $passwordHasher->hashPassword(
                $utilisateur,
                $form->get('plainPassword')->getData()
            );
            $utilisateur->setPassword($hashedPassword);

            $resetToken->setUtilise(true);
            $resetToken->setDateUtilisation(new \DateTime());

            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a ete reinitialise avec succes !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/password_reset.html.twig', [
            'form' => $form->createView(),
            'token' => $token,
        ]);
    }
}
