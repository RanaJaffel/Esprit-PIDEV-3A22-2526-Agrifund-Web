<?php

namespace App\Controller\Agriculteur;

use App\Entity\Agriculteur;
use App\Form\AgriculteurProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur/profile')]
#[IsGranted('ROLE_AGRICULTEUR')]
class AgriculteurProfileController extends AbstractController
{
    #[Route('/', name: 'agriculteur_profile_show')]
    public function show(): Response
    {
        $utilisateur = $this->getUser();
        
        return $this->render('agriculteur/profile/show.html.twig', [
            'utilisateur' => $utilisateur,
            'agriculteur' => $utilisateur->getAgriculteur(),
        ]);
    }

    #[Route('/edit', name: 'agriculteur_profile_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $utilisateur = $this->getUser();
        
        // S'assurer que l'agriculteur existe
        if (!$utilisateur->getAgriculteur()) {
            $agriculteur = new Agriculteur();
            $agriculteur->setUtilisateur($utilisateur);
            $agriculteur->setStatuscompte('en_attente');
            $utilisateur->setAgriculteur($agriculteur);
            $em->persist($agriculteur);
        }
        
        $form = $this->createForm(AgriculteurProfileType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du changement de mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
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

            // Symfony gère automatiquement la mise à jour de l'entité Agriculteur
            // grâce au formulaire imbriqué - pas besoin de code supplémentaire

            $em->flush();

            $this->addFlash('success', 'Profil modifié avec succès !');
            return $this->redirectToRoute('agriculteur_profile_show');
        }

        return $this->render('agriculteur/profile/edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
        ]);
    }
}