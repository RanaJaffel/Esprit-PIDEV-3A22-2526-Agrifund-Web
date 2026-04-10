<?php

namespace App\Controller\Security;

use App\Entity\Utilisateur;
use App\Entity\Agriculteur;
use App\Entity\Banque;
use App\Form\AgriculteurRegistrationType;
use App\Form\BanqueRegistrationType;
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
        EntityManagerInterface $em
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $utilisateur = new Utilisateur();
        $agriculteur = new Agriculteur();
        $utilisateur->setAgriculteur($agriculteur);
        
        $form = $this->createForm(AgriculteurRegistrationType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Récupération du mot de passe depuis le formulaire (champ non mappé)
                $plainPassword = $form->get('plainPassword')->getData();
                
                // Hash du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setPassword($hashedPassword);

                // L'agriculteur est déjà associé via le formulaire
                $agriculteur = $utilisateur->getAgriculteur();
                $agriculteur->setUtilisateur($utilisateur);
                
                // Statut par défaut
                $agriculteur->setStatuscompte('en_attente');
                $agriculteur->setCompteverifie(false);

                // Sauvegarde
                $em->persist($utilisateur);
                $em->flush();

                $this->addFlash('success', 'Inscription réussie ! Votre compte est en attente de validation par un administrateur.');
                
                return $this->redirectToRoute('app_login');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
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
        EntityManagerInterface $em
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $utilisateur = new Utilisateur();
        $banque = new Banque();
        $utilisateur->setBanque($banque);
        
        $form = $this->createForm(BanqueRegistrationType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Récupération du mot de passe depuis le formulaire (champ non mappé)
                $plainPassword = $form->get('plainPassword')->getData();
                
                // Hash du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setPassword($hashedPassword);

                // La banque est déjà associée via le formulaire
                $banque = $utilisateur->getBanque();
                $banque->setUtilisateur($utilisateur);
                
                // Convertir le code banque en majuscules
                $banque->setCodebanque(strtoupper($banque->getCodebanque()));
                
                // Statut par défaut
                $banque->setStatusCompte('en_attente');
                $banque->setCompteVerfiee(false);

                // Sauvegarde
                $em->persist($utilisateur);
                $em->flush();

                $this->addFlash('success', 'Inscription réussie ! Votre compte est en attente de validation par un administrateur.');
                
                return $this->redirectToRoute('app_login');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
            }
        }

        return $this->render('security/register_banque.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}