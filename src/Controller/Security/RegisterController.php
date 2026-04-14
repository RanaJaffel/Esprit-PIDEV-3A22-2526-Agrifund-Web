<?php
// src/Controller/Security/RegisterController.php

namespace App\Controller\Security;

use App\Entity\Utilisateur;
use App\Entity\Agriculteur;
use App\Entity\Banque;
use App\Form\AgriculteurRegistrationType;
use App\Form\BanqueRegistrationType;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function chooseType(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/register_choice.html.twig');
    }

    #[Route('/register/agriculteur', name: 'app_register_agriculteur')]
    public function registerAgriculteur(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        EmailVerificationService $emailVerification
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $utilisateur = new Utilisateur();
        $agriculteur = new Agriculteur();
        
        // Établir la relation des DEUX côtés
        $utilisateur->setAgriculteur($agriculteur);
        $agriculteur->setUtilisateur($utilisateur);
        
        $form = $this->createForm(AgriculteurRegistrationType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $plainPassword = $form->get('plainPassword')->getData();
                
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setPassword($hashedPassword);
                
                // Email non vérifié par défaut
                $utilisateur->setIsVerified(false);

                // Re-assurer la relation
                $agriculteur = $utilisateur->getAgriculteur();
                $agriculteur->setUtilisateur($utilisateur);
                
                // Statut par défaut
                $agriculteur->setStatuscompte('en_attente');
                $agriculteur->setCompteverifie(false);

                // Persister les DEUX entités
                $em->persist($utilisateur);
                $em->persist($agriculteur);
                $em->flush();

                // Envoyer l'email de vérification
                $emailVerification->sendVerificationEmail($utilisateur);

                $this->addFlash('success', 'Inscription réussie ! Un email de vérification a été envoyé à ' . $utilisateur->getEmail());
                
                return $this->redirectToRoute('app_login');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription : ' . $e->getMessage());
            }
        }

        return $this->render('security/register_agriculteur.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register/banque', name: 'app_register_banque')]
    public function registerBanque(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        EmailVerificationService $emailVerification
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $utilisateur = new Utilisateur();
        $banque = new Banque();
        
        // Établir la relation des DEUX côtés
        $utilisateur->setBanque($banque);
        $banque->setUtilisateur($utilisateur);
        
        $form = $this->createForm(BanqueRegistrationType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $plainPassword = $form->get('plainPassword')->getData();
                
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setPassword($hashedPassword);
                
                // Email non vérifié par défaut
                $utilisateur->setIsVerified(false);

                // Re-assurer la relation
                $banque = $utilisateur->getBanque();
                $banque->setUtilisateur($utilisateur);
                
                // Convertir le code banque en majuscules
                $banque->setCodebanque(strtoupper($banque->getCodebanque()));
                
                // Statut par défaut
                $banque->setStatusCompte('en_attente');
                $banque->setCompteVerfiee(false);

                // Persister les DEUX entités
                $em->persist($utilisateur);
                $em->persist($banque);
                $em->flush();

                // Envoyer l'email de vérification
                $emailVerification->sendVerificationEmail($utilisateur);

                $this->addFlash('success', 'Inscription réussie ! Un email de vérification a été envoyé à ' . $utilisateur->getEmail());
                
                return $this->redirectToRoute('app_login');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription : ' . $e->getMessage());
            }
        }

        return $this->render('security/register_banque.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}