<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Utilisateur;
use App\Form\AdminType;
use App\Form\UtilisateurProfileType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'admin_users_index')]
    public function index(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search');
        $type = $request->query->get('type');

        $utilisateurs = $utilisateurRepository->searchUtilisateurs($search, $type);

        $pagination = $paginator->paginate(
            $utilisateurs,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/utilisateurs/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
            'type' => $type,
        ]);
    }

    #[Route('/add-admin', name: 'admin_users_add_admin')]
    public function addAdmin(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $utilisateur = new Utilisateur();
        $admin = new Admin();
        
        $form = $this->createForm(AdminType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $utilisateur,
                $utilisateur->getPlainPassword()
            );
            $utilisateur->setPassword($hashedPassword);

            // Association
            $admin->setUtilisateur($utilisateur);
            $utilisateur->setAdmin($admin);

            $em->persist($utilisateur);
            $em->persist($admin);
            $em->flush();

            $this->addFlash('success', 'Administrateur ajouté avec succès !');
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/utilisateurs/add_admin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_users_edit')]
    public function edit(
        Utilisateur $utilisateur,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(UtilisateurProfileType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de photo
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
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

            $this->addFlash('success', 'Utilisateur modifié avec succès !');
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/utilisateurs/edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(
        Utilisateur $utilisateur,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(), $request->request->get('_token'))) {
            // Empêcher la suppression de son propre compte
            if ($utilisateur->getId() === $this->getUser()->getId()) {
                $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte !');
                return $this->redirectToRoute('admin_users_index');
            }

            // Supprimer la photo si elle existe
            if ($utilisateur->getPhoto()) {
                $photoPath = $this->getParameter('kernel.project_dir') . '/public/' . $utilisateur->getPhoto();
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }

            $em->remove($utilisateur);
            $em->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_users_index');
    }
}