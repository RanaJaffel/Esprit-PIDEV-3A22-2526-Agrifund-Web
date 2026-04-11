<?php

namespace App\Controller;

use App\Entity\Capteur;
use App\Form\CapteurType;
use App\Repository\CapteurRepository;
use App\Repository\ReleveTerrainRepository;
use App\Repository\RapportJournalierRepository;
use App\Repository\ReleveHebdomadaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/capteur')]
class CapteurController extends AbstractController
{
    // ==========================================
    // 📡 LISTE DES CAPTEURS (Agriculteur)
    // ==========================================
    #[Route('/', name: 'capteur_index')]
    public function index(
        CapteurRepository $capteurRepo
    ): Response {
        $user = $this->getUser();
        $capteurs = $capteurRepo->findByUser($user->getId());

        // ✅ Calculs en PHP
        $projetsUniques = array_unique(
            array_map(fn($c) => $c->getIdproject(), $capteurs)
        );
        $nbActifs = count(array_filter(
            $capteurs, fn($c) => $c->getStatut() === 'ACTIF'
        ));
        $nbInactifs = count(array_filter(
            $capteurs, fn($c) => $c->getStatut() === 'INACTIF'
        ));

        return $this->render('agriculteur/capteur/index.html.twig', [
            'capteurs'   => $capteurs,
            'nbProjets'  => count($projetsUniques),
            'nbActifs'   => $nbActifs,
            'nbInactifs' => $nbInactifs,
        ]);
    }

    // ==========================================
    // ➕ AJOUTER UN CAPTEUR
    // ==========================================
   #[Route('/new', name: 'capteur_new')]
public function new(
    Request $request,
    EntityManagerInterface $em
): Response {
    $user    = $this->getUser();
    $capteur = new Capteur();
    $capteur->setIdUser($user->getId());

    $form = $this->createForm(CapteurType::class, $capteur);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($capteur);
        $em->flush();
        $this->addFlash('success', '✅ Capteur ajouté !');
        return $this->redirectToRoute('capteur_index');
    }

    return $this->render('agriculteur/capteur/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    // ==========================================
    // ✏️ MODIFIER UN CAPTEUR
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

        $form = $this->createForm(CapteurType::class, $capteur);
        $form->handleRequest($request);

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
    // 🗑️ SUPPRIMER UN CAPTEUR
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

        if ($this->isCsrfTokenValid(
            'delete' . $id,
            $request->request->get('_token')
        )) {
            $em->remove($capteur);
            $em->flush();
            $this->addFlash('success', '🗑️ Capteur supprimé !');
        }

        return $this->redirectToRoute('capteur_index');
    }

    // ==========================================
    // 📊 DASHBOARD IoT
    // ==========================================
    #[Route('/dashboard/{idproject}', name: 'capteur_dashboard')]
public function dashboard(
    int $idproject,
    CapteurRepository $capteurRepo,
    ReleveTerrainRepository $releveRepo,
    RapportJournalierRepository $rapportRepo,
    ReleveHebdomadaireRepository $hebdoRepo
): Response {
    $user = $this->getUser();

    // 🔐 Sécurité : vérifier que ce projet appartient à cet agriculteur
    $count = $capteurRepo->count([
        'idproject' => $idproject,
        'idUser'    => $user->getId(),
    ]);

    if ($count === 0) {
        throw $this->createAccessDeniedException("Accès refusé : projet non autorisé.");
    }

    // Capteurs du projet (pour cet utilisateur)
    $capteurs = $capteurRepo->findBy([
        'idproject' => $idproject,
        'idUser'    => $user->getId(),
    ]);

    $dernieresMesures = $releveRepo->findDernieresMesures($idproject);

    $rapportsJour = $rapportRepo->findByProjectAndDate(
        $idproject,
        new \DateTime('today')
    );

    // ✅ ICI la vraie correction
    $releveHebdo = $hebdoRepo->findLatestByProject($idproject);

    return $this->render('agriculteur/capteur/dashboard.html.twig', [
        'capteurs'         => $capteurs,
        'dernieresMesures' => $dernieresMesures,
        'rapportsJour'     => $rapportsJour,
        'releveHebdo'      => $releveHebdo,
        'idproject'        => $idproject,
    ]);
}

    // ==========================================
    // 📅 RAPPORT JOURNALIER
    // ==========================================
    #[Route('/rapport/{idproject}', name: 'capteur_rapport')]
    public function rapport(
        int $idproject,
        Request $request,
        RapportJournalierRepository $rapportRepo
    ): Response {
        $dateStr  = $request->query->get(
            'date',
            (new \DateTime())->format('Y-m-d')
        );
        $date     = new \DateTime($dateStr);
        $rapports = $rapportRepo->findByProjectAndDate($idproject, $date);

        return $this->render('agriculteur/capteur/rapport.html.twig', [
            'rapports'  => $rapports,
            'date'      => $date,
            'idproject' => $idproject,
        ]);
    }

    // ==========================================
    // 📅 RELEVÉ HEBDOMADAIRE
    // ==========================================
    #[Route('/releve-hebdo/{idproject}', name: 'capteur_releve_hebdo')]
    public function releveHebdo(
        int $idproject,
        ReleveHebdomadaireRepository $hebdoRepo
    ): Response {
        $releves = $hebdoRepo->findByProject($idproject);

        return $this->render('agriculteur/capteur/releve_hebdo.html.twig', [
            'releves'   => $releves,
            'idproject' => $idproject,
        ]);
    }

    // ==========================================
    // 🔐 ADMIN — TOUS LES CAPTEURS
    // ==========================================
    #[Route('/admin', name: 'admin_capteur_index')]
    public function adminIndex(
        CapteurRepository $capteurRepo
    ): Response {
        $capteurs = $capteurRepo->findAll();

        $projetsUniques = array_unique(
            array_map(fn($c) => $c->getIdproject(), $capteurs)
        );
        $nbActifs = count(array_filter(
            $capteurs, fn($c) => $c->getStatut() === 'ACTIF'
        ));
        $nbInactifs = count(array_filter(
            $capteurs, fn($c) => $c->getStatut() === 'INACTIF'
        ));

        return $this->render('admin/capteur/index.html.twig', [
            'capteurs'   => $capteurs,
            'nbProjets'  => count($projetsUniques),
            'nbActifs'   => $nbActifs,
            'nbInactifs' => $nbInactifs,
        ]);
    }

    // ==========================================
    // 🔐 ADMIN — MODIFIER CAPTEUR
    // ==========================================
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
    #[Route('/admin/delete/{id}', 
             name: 'admin_capteur_delete', 
             methods: ['POST'])]
    public function adminDelete(
        int $id,
        CapteurRepository $capteurRepo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $capteur = $capteurRepo->find($id);

        if ($capteur && $this->isCsrfTokenValid(
            'delete' . $id,
            $request->request->get('_token')
        )) {
            $em->remove($capteur);
            $em->flush();
            $this->addFlash('success', '🗑️ Capteur supprimé !');
        }

        return $this->redirectToRoute('admin_capteur_index');
    }
}