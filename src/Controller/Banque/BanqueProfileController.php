<?php
// src/Controller/Banque/BanqueProfileController.php

namespace App\Controller\Banque;

use App\Form\BanqueProfileType;
use App\Form\Parametres2faType;
use App\Service\TwoFactorAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/banque/profile')]
#[IsGranted('ROLE_BANQUE')]
class BanqueProfileController extends AbstractController
{
    #[Route('/', name: 'banque_profile_show')]
    public function show(): Response
    {
        return $this->render('banque/profile/show.html.twig', [
            'utilisateur' => $this->getUser(),
            'banque' => $this->getUser()->getBanque(),
        ]);
    }

    #[Route('/edit', name: 'banque_profile_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $utilisateur = $this->getUser();
        $banque = $utilisateur->getBanque();
        
        $banqueData = [
            'addresseSiege' => $banque->getAddresseSiege(),
            'representantLegal' => $banque->getRepresentantLegal(),
            'adresseAgence' => $banque->getAdresseAgence(),
            'siteweb' => $banque->getSiteweb(),
        ];
        
        $form = $this->createForm(BanqueProfileType::class, $utilisateur);
        $form->get('banque')->setData($banqueData);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setPassword($hashedPassword);
            }

            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
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

            $logoFile = $form->get('banque')->get('logoFile')->getData();
            if ($logoFile) {
                if ($banque->getLogo()) {
                    $oldLogoPath = $this->getParameter('kernel.project_dir') . '/public/' . $banque->getLogo();
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }

                $newLogoFilename = 'logo_banque_' . $banque->getId() . '_' . date('Ymd_His') . '.' . $logoFile->guessExtension();
                
                try {
                    $logoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/logos',
                        $newLogoFilename
                    );
                    $banque->setLogo('uploads/logos/' . $newLogoFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du logo.');
                }
            }

            $banqueFormData = $form->get('banque')->getData();
            $banque->setAddresseSiege($banqueFormData['addresseSiege'] ?? null);
            $banque->setRepresentantLegal($banqueFormData['representantLegal'] ?? null);
            $banque->setAdresseAgence($banqueFormData['adresseAgence'] ?? null);
            $banque->setSiteweb($banqueFormData['siteweb'] ?? null);

            $em->flush();

            $this->addFlash('success', 'Profil modifié avec succès !');
            return $this->redirectToRoute('banque_profile_show');
        }

        return $this->render('banque/profile/edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
            'banque' => $banque,
        ]);
    }

    #[Route('/2fa', name: 'banque_profile_2fa')]
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
                
                return $this->redirectToRoute('banque_profile_show');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification des paramètres.');
            }
        }
        
        return $this->render('banque/profile/2fa.html.twig', [
            'form' => $form->createView(),
            'parametres' => $parametres,
            'utilisateur' => $utilisateur
        ]);
    }
}