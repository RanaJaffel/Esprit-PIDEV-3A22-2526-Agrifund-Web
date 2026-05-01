<?php

namespace App\Controller\Admin;

use App\Repository\AgriculteurRepository;
use App\Repository\BanqueRepository;
use App\Repository\DocumentRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'admin_index', methods: ['GET'])]
    public function redirectDashboard(): Response
    {
        return $this->redirectToRoute('admin_dashboard');
    }
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(
        UtilisateurRepository $utilisateurRepository,
        AgriculteurRepository $agriculteurRepository,
        BanqueRepository $banqueRepository,
        DocumentRepository $documentRepository,
        MessageRepository $messageRepository
    ): Response {
        // Statistiques utilisateurs
        $statsUtilisateurs = $utilisateurRepository->countByType();
        
        // Statistiques agriculteurs
        $statsAgriculteurs = $agriculteurRepository->getStatistics();
        
        // Statistiques banques
        $statsBanques = $banqueRepository->getStatistics();
        
        // Statistiques documents
        $statsDocuments = $documentRepository->getStatistics();
        
        // Documents en attente
        $documentsEnAttente = $documentRepository->findPendingDocuments();
        
        // Agriculteurs en attente
        $agriculteursEnAttente = $agriculteurRepository->findPendingVerification();
        
        // Banques en attente
        $banquesEnAttente = $banqueRepository->findPendingVerification();
        
        // Utilisateurs récents
        $utilisateursRecents = $utilisateurRepository->findRecentUsers(5);
        
        // Messages non lus
        $messagesNonLus = $messageRepository->countAllUnreadMessages($this->getUser()->getId());

        return $this->render('admin/dashboard.html.twig', [
            'stats_utilisateurs' => $statsUtilisateurs,
            'stats_agriculteurs' => $statsAgriculteurs,
            'stats_banques' => $statsBanques,
            'stats_documents' => $statsDocuments,
            'documents_en_attente' => $documentsEnAttente,
            'agriculteurs_en_attente' => $agriculteursEnAttente,
            'banques_en_attente' => $banquesEnAttente,
            'utilisateurs_recents' => $utilisateursRecents,
            'messages_non_lus' => $messagesNonLus,
        ]);
    }
}