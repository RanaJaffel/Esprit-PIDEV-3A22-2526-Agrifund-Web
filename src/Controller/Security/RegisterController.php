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
        
        // IMPORTANT : Établir la relation des DEUX côtés
        $utilisateur->setAgriculteur($agriculteur);
        $agriculteur->setUtilisateur($utilisateur);
        
        $form = $this->createForm(AgriculteurRegistrationType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // NE PAS utiliser try/catch pendant le debug
            $plainPassword = $form->get('plainPassword')->getData();
            
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setPassword($hashedPassword);

            // Re-assurer la relation (au cas où le formulaire l'aurait modifiée)
            $agriculteur = $utilisateur->getAgriculteur();
            $agriculteur->setUtilisateur($utilisateur);
            
            // Statut par défaut
            $agriculteur->setStatuscompte('en_attente');
            $agriculteur->setCompteverifie(false);

            // Persister les DEUX entités explicitement
            $em->persist($utilisateur);
            $em->persist($agriculteur);
            $em->flush();

            $this->addFlash('success', 'Inscription réussie ! Votre compte est en attente de validation.');
            
            return $this->redirectToRoute('app_login');
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
        
        // IMPORTANT : Établir la relation des DEUX côtés
        $utilisateur->setBanque($banque);
        $banque->setUtilisateur($utilisateur);
        
        $form = $this->createForm(BanqueRegistrationType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // NE PAS utiliser try/catch pendant le debug
            $plainPassword = $form->get('plainPassword')->getData();
            
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setPassword($hashedPassword);

            // Re-assurer la relation
            $banque = $utilisateur->getBanque();
            $banque->setUtilisateur($utilisateur);
            
            // Convertir le code banque en majuscules
            $banque->setCodebanque(strtoupper($banque->getCodebanque()));
            
            // Statut par défaut
            $banque->setStatusCompte('en_attente');
            $banque->setCompteVerfiee(false);

            // Persister les DEUX entités explicitement
            $em->persist($utilisateur);
            $em->persist($banque);
            $em->flush();

            $this->addFlash('success', 'Inscription réussie ! Votre compte est en attente de validation.');
            
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register_banque.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}