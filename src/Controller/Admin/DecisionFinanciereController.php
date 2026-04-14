<?php

namespace App\Controller\Admin;

use App\Entity\DecisionFinanciere;
use App\Form\DecisionFinanciereType;
use App\Repository\DecisionFinanciereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/decision', name: 'admin_decision_')]
class DecisionFinanciereController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        DecisionFinanciereRepository $repo,
        \App\Repository\UtilisateurRepository $utilisateurRepository,
        \App\Repository\AgriculteurRepository $agriculteurRepository,
        \App\Repository\BanqueRepository $banqueRepository,
        \App\Repository\DocumentRepository $documentRepository
    ): Response {
        // Statistiques pour dashboard
        $statsUtilisateurs = $utilisateurRepository->countByType();
        $statsAgriculteurs = $agriculteurRepository->getStatistics();
        $statsBanques = $banqueRepository->getStatistics();
        $statsDocuments = $documentRepository->getStatistics();
        $documentsEnAttente = $documentRepository->findPendingDocuments();
        $agriculteursEnAttente = $agriculteurRepository->findPendingVerification();
        $banquesEnAttente = $banqueRepository->findPendingVerification();
        $utilisateursRecents = $utilisateurRepository->findRecentUsers(5);

        return $this->render('admin/decision/index.html.twig', [
            'decisions' => $repo->findAll(),
            'stats_utilisateurs' => $statsUtilisateurs,
            'stats_agriculteurs' => $statsAgriculteurs,
            'stats_banques' => $statsBanques,
            'stats_documents' => $statsDocuments,
            'documents_en_attente' => $documentsEnAttente,
            'agriculteurs_en_attente' => $agriculteursEnAttente,
            'banques_en_attente' => $banquesEnAttente,
            'utilisateurs_recents' => $utilisateursRecents,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $decision = new DecisionFinanciere();
        $form = $this->createForm(DecisionFinanciereType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($decision);
            $em->flush();
            $this->addFlash('success', 'Décision créée !');
            return $this->redirectToRoute('admin_decision_index');
        }

        return $this->render('admin/decision/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Request $request, DecisionFinanciere $decision, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DecisionFinanciereType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Décision modifiée !');
            return $this->redirectToRoute('admin_decision_index');
        }

        return $this->render('admin/decision/edit.html.twig', [
            'form' => $form->createView(),
            'decision' => $decision,
        ]);
    }

    #[Route('/{id}/show', name: 'show')]
    public function show(DecisionFinanciere $decision): Response
    {
        return $this->render('admin/decision/show.html.twig', [
            'decision' => $decision,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, DecisionFinanciere $decision, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$decision->getIdDecision(), $request->request->get('_token'))) {
            $em->remove($decision);
            $em->flush();
            $this->addFlash('success', 'Décision supprimée !');
        }
        return $this->redirectToRoute('admin_decision_index');
    }
}