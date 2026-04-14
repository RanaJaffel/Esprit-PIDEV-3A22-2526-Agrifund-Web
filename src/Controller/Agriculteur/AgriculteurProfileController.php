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
            $em->flush();
        }
        
        $form = $this->createForm(AgriculteurProfileType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Gestion du changement de mot de passe
                $plainPassword = $form->get('plainPassword')->getData();
                if ($plainPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                    $utilisateur->setPassword($hashedPassword);
                }

                // Gestion de l'upload de photo
                $photoFile = $form->get('photoFile')->getData();
                if ($photoFile) {
                    // Vérifier si le fichier est valide
                    if ($photoFile->isValid()) {
                        // Supprimer l'ancienne photo si elle existe
                        if ($utilisateur->getPhoto()) {
                            $oldPhotoPath = $this->getParameter('kernel.project_dir') . '/public/' . $utilisateur->getPhoto();
                            if (file_exists($oldPhotoPath)) {
                                @unlink($oldPhotoPath);
                            }
                        }

                        $extension = $photoFile->guessExtension();
                        if (!$extension) {
                            $extension = $photoFile->getClientOriginalExtension();
                        }

                        $newFilename = 'user_' . $utilisateur->getId() . '_' . uniqid() . '_' . date('Ymd_His') . '.' . $extension;
                        
                        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/photos';
                        
                        // Créer le dossier s'il n'existe pas
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        $photoFile->move($uploadDir, $newFilename);
                        $utilisateur->setPhoto('uploads/photos/' . $newFilename);
                    } else {
                        $this->addFlash('error', 'Le fichier photo est invalide.');
                        return $this->redirectToRoute('agriculteur_profile_edit');
                    }
                }

                // Récupérer l'entité Agriculteur depuis le formulaire
                $agriculteur = $utilisateur->getAgriculteur();
                
                // S'assurer que la relation bidirectionnelle est correcte
                if ($agriculteur && !$agriculteur->getUtilisateur()) {
                    $agriculteur->setUtilisateur($utilisateur);
                }

                $em->flush();

                $this->addFlash('success', 'Profil modifié avec succès !');
                return $this->redirectToRoute('agriculteur_profile_show');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification du profil : ' . $e->getMessage());
                return $this->redirectToRoute('agriculteur_profile_edit');
            }
        }

        return $this->render('agriculteur/profile/edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
        ]);
    }
}