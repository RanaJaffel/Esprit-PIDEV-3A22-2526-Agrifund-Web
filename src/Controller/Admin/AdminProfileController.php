<?php
// src/Controller/Admin/AdminProfileController.php

namespace App\Controller\Admin;

use App\Form\Parametres2faType;
use App\Form\UtilisateurProfileType;
use App\Service\TwoFactorAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/profile')]
#[IsGranted('ROLE_ADMIN')]
class AdminProfileController extends AbstractController
{
    #[Route('/', name: 'admin_profile_show')]
    public function show(): Response
    {
        return $this->render('admin/profile/show.html.twig', [
            'utilisateur' => $this->getUser(),
        ]);
    }

    #[Route('/edit', name: 'admin_profile_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $utilisateur = $this->getUser();
        
        $form = $this->createForm(UtilisateurProfileType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du changement de mot de passe
            if ($form->get('plainPassword')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $utilisateur,
                    $form->get('plainPassword')->getData()
                );
                $utilisateur->setPassword($hashedPassword);
            }

            // Gestion de l'upload de photo
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                // Supprimer l'ancienne photo si elle existe
                if ($utilisateur->getPhoto()) {
                    $oldPhotoPath = $this->getParameter('kernel.project_dir') . '/public/' . $utilisateur->getPhoto();
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $newFilename = 'user_' . $utilisateur->getId() . '_' . date('Ymd_His') . '.' . $photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/photos',
                        $newFilename
                    );
                    $utilisateur->setPhoto('uploads/photos/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                }
            }

            $em->flush();

            $this->addFlash('success', 'Profil modifié avec succès !');
            return $this->redirectToRoute('admin_profile_show');
        }

        return $this->render('admin/profile/edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/2fa', name: 'admin_profile_2fa')]
    public function manage2FA(
        Request $request,
        EntityManagerInterface $em,
        TwoFactorAuthService $twoFactorService
    ): Response {
        $utilisateur = $this->getUser();
        $parametres = $utilisateur->getParametres2fa();
        
        if (!$parametres) {
            $parametres = new \App\Entity\Parametres2fa();
            $parametres->setUtilisateur($utilisateur);
        }
        
        $form = $this->createForm(Parametres2faType::class, $parametres);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $twoFactorService->activerOuDesactiver2FA(
                    $utilisateur,
                    $parametres->isEstActive(),
                    $parametres->getTelephone2fa(),
                    $parametres->getMethodePreferee()
                );
                
                $this->addFlash('success', 
                    $parametres->isEstActive() 
                        ? 'Authentification à deux facteurs activée avec succès !' 
                        : 'Authentification à deux facteurs désactivée.'
                );
                
                return $this->redirectToRoute('admin_profile_show');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification des paramètres.');
            }
        }
        
        return $this->render('admin/profile/2fa.html.twig', [
            'form' => $form->createView(),
            'parametres' => $parametres,
            'utilisateur' => $utilisateur
        ]);
    }
}