<?php

namespace App\Controller;

use App\Entity\Capteur;
use App\Form\CapteurType;
use App\Repository\CapteurRepository;
use App\Repository\NotificationRepository;
use App\Repository\ReleveTerrainRepository;
use App\Repository\RapportJournalierRepository;
use App\Repository\ReleveHebdomadaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/capteur')]
class CapteurController extends AbstractController
{
    // ==========================================
    // 📡 LISTE DES CAPTEURS (Agriculteur)
    // ==========================================
    #[Route('/', name: 'capteur_index')]
    public function index(CapteurRepository $capteurRepo): Response
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }

        $capteurs = $capteurRepo->findByUser((int)$user->getId());

        $projetsUniques = array_unique(array_map(fn($c) => $c->getIdproject(), $capteurs));
        $nbActifs = count(array_filter($capteurs, fn($c) => $c->getStatut() === 'ACTIF'));
        $nbInactifs = count(array_filter($capteurs, fn($c) => $c->getStatut() === 'INACTIF'));

        return $this->render('agriculteur/capteur/index.html.twig', [
            'capteurs'   => $capteurs,
            'nbProjets'  => count($projetsUniques),
            'nbActifs'   => $nbActifs,
            'nbInactifs' => $nbInactifs,
        ]);
    }

    // ==========================================
    // ➕ AJOUTER UN CAPTEUR (Agriculteur)
    // ==========================================
    #[Route('/new', name: 'capteur_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }

        $capteur = new Capteur();
        $capteur->setIdUser((int)$user->getId());

        $form = $this->createForm(CapteurType::class, $capteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($capteur);
                $em->flush();

                $this->addFlash('success', '✅ Capteur ajouté !');
                return $this->redirectToRoute('capteur_index');
            } catch (\Throwable $e) {
                $this->addFlash('error', "Erreur lors de l'enregistrement : " . $e->getMessage());
            }
        }

        return $this->render('agriculteur/capteur/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ==========================================
    // ✏️ MODIFIER UN CAPTEUR (Agriculteur)
    // ==========================================
    #[Route('/edit/{id}', name: 'capteur_edit')]
    public function edit(
        int $id,
        Request $request,
        CapteurRepository $capteurRepo,
        EntityManagerInterface $em
    ): Response {
        $capteur = $capteurRepo->find($id);
        if (!$capteur) {
            throw $this->createNotFoundException('Capteur introuvable');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if (!$user || !method_exists($user, 'getId')) {
                throw $this->createAccessDeniedException("Vous devez être connecté.");
            }
            if ($capteur->getIdUser() !== (int)$user->getId()) {
                throw $this->createAccessDeniedException("Accès refusé.");
            }
        }

        $form = $this->createForm(CapteurType::class, $capteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Capteur modifié !');
            return $this->redirectToRoute('capteur_index');
        }

        return $this->render('agriculteur/capteur/edit.html.twig', [
            'form'    => $form->createView(),
            'capteur' => $capteur,
        ]);
    }

    // ==========================================
    // 🗑️ SUPPRIMER UN CAPTEUR (Agriculteur)
    // ==========================================
    #[Route('/delete/{id}', name: 'capteur_delete', methods: ['POST'])]
    public function delete(
        int $id,
        CapteurRepository $capteurRepo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $capteur = $capteurRepo->find($id);
        if (!$capteur) {
            throw $this->createNotFoundException('Capteur introuvable');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if (!$user || !method_exists($user, 'getId')) {
                throw $this->createAccessDeniedException("Vous devez être connecté.");
            }
            if ($capteur->getIdUser() !== (int)$user->getId()) {
                throw $this->createAccessDeniedException("Accès refusé.");
            }
        }

        if ($this->isCsrfTokenValid('delete' . $id, (string)$request->request->get('_token'))) {
            $em->remove($capteur);
            $em->flush();
            $this->addFlash('success', '🗑️ Capteur supprimé !');
        }

        return $this->redirectToRoute('capteur_index');
    }

    // ==========================================
    // 📊 DASHBOARD IoT + Notifications
    // ==========================================
    #[Route('/dashboard/{idproject}', name: 'capteur_dashboard')]
    public function dashboard(
        int $idproject,
        CapteurRepository $capteurRepo,
        ReleveTerrainRepository $releveRepo,
        RapportJournalierRepository $rapportRepo,
        ReleveHebdomadaireRepository $hebdoRepo,
        NotificationRepository $notifRepo
    ): Response {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }

        // 🔐 sécurité projet via capteurs
        $count = $capteurRepo->count([
            'idproject' => $idproject,
            'idUser'    => (int)$user->getId(),
        ]);

        if ($count === 0 && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Accès refusé : projet non autorisé.");
        }

        $capteurs = $this->isGranted('ROLE_ADMIN')
            ? $capteurRepo->findBy(['idproject' => $idproject])
            : $capteurRepo->findBy(['idproject' => $idproject, 'idUser' => (int)$user->getId()]);

        $dernieresMesures = $releveRepo->findDernieresMesures($idproject);
        $rapportsJour = $rapportRepo->findByProjectAndDate($idproject, new \DateTime('today'));
        $releveHebdo = $hebdoRepo->findLatestByProject($idproject);

        $latestNotifs = $notifRepo->findUnreadByProjet($idproject, 8);
        $unreadCount  = $notifRepo->countUnreadByProjet($idproject);

        return $this->render('agriculteur/capteur/dashboard.html.twig', [
            'capteurs'         => $capteurs,
            'dernieresMesures' => $dernieresMesures,
            'rapportsJour'     => $rapportsJour,
            'releveHebdo'      => $releveHebdo,
            'idproject'        => $idproject,
            'latestNotifs'     => $latestNotifs,
            'unreadCount'      => $unreadCount,
        ]);
    }

    // ==========================================
    // 🔐 ADMIN — TOUS LES CAPTEURS
    // ==========================================
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin_capteur_index')]
    public function adminIndex(CapteurRepository $capteurRepo): Response
    {
        $capteurs = $capteurRepo->findAll();

        $projetsUniques = array_unique(array_map(fn($c) => $c->getIdproject(), $capteurs));
        $nbActifs = count(array_filter($capteurs, fn($c) => $c->getStatut() === 'ACTIF'));
        $nbInactifs = count(array_filter($capteurs, fn($c) => $c->getStatut() === 'INACTIF'));

        return $this->render('admin/capteur/index.html.twig', [
            'capteurs'   => $capteurs,
            'nbProjets'  => count($projetsUniques),
            'nbActifs'   => $nbActifs,
            'nbInactifs' => $nbInactifs,
        ]);
    }

    // ==========================================
    // 🔐 ADMIN — LANCER ANALYSE IA (Module 4)
    // ==========================================
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/ai-scan', name: 'admin_ai_scan', methods: ['POST'])]
    public function adminAiScan(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('ai_scan', (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_capteur_index');
        }

        try {
            $process = new Process(['php', 'bin/console', 'iot:ai-scan'], $this->getParameter('kernel.project_dir'));
            $process->setTimeout(120);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->addFlash('error', "Erreur IA : " . $process->getErrorOutput());
            } else {
                $out = trim($process->getOutput());
                $this->addFlash('success', "✅ Analyse IA terminée : " . ($out ?: 'OK'));
            }
        } catch (\Throwable $e) {
            $this->addFlash('error', "Exception IA : " . $e->getMessage());
        }

        return $this->redirectToRoute('admin_capteur_index');
    }

    // ==========================================
    // 🔐 ADMIN — MODIFIER CAPTEUR
    // ==========================================
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/edit/{id}', name: 'admin_capteur_edit')]
    public function adminEdit(
        int $id,
        Request $request,
        CapteurRepository $capteurRepo,
        EntityManagerInterface $em
    ): Response {
        $capteur = $capteurRepo->find($id);

        if (!$capteur) {
            throw $this->createNotFoundException('Capteur introuvable');
        }

        $form = $this->createForm(CapteurType::class, $capteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Capteur modifié !');
            return $this->redirectToRoute('admin_capteur_index');
        }

        return $this->render('admin/capteur/edit.html.twig', [
            'form'    => $form->createView(),
            'capteur' => $capteur,
        ]);
    }

    // ==========================================
    // 🔐 ADMIN — SUPPRIMER CAPTEUR
    // ==========================================
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/delete/{id}', name: 'admin_capteur_delete', methods: ['POST'])]
    public function adminDelete(
        int $id,
        CapteurRepository $capteurRepo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $capteur = $capteurRepo->find($id);

        if ($capteur && $this->isCsrfTokenValid('delete' . $id, (string)$request->request->get('_token'))) {
            $em->remove($capteur);
            $em->flush();
            $this->addFlash('success', '🗑️ Capteur supprimé !');
        }

        return $this->redirectToRoute('admin_capteur_index');
    }
}
