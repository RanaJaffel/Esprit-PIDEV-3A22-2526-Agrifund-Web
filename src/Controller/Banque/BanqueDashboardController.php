<?php

namespace App\Controller\Banque;

use App\Repository\DocumentRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/banque')]
#[IsGranted('ROLE_BANQUE')]
class BanqueDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'banque_dashboard')]
    public function index(
        DocumentRepository $documentRepository,
        MessageRepository $messageRepository
    ): Response {
        $currentUser = $this->getUser();
        $banque = $currentUser->getBanque();

        // Statistiques documents
        $documentsStats = $documentRepository->getDashboardStatsForUtilisateur($currentUser);
        $documentsRecents = $documentRepository->findRecentByUtilisateur($currentUser, 5);

        // Messages non lus
        $messagesNonLus = $messageRepository->countAllUnreadMessages($currentUser->getId());

        // Statut du compte
        $compteStatut = [
            'status' => $banque->getStatusCompte(),
            'verifie' => $banque->isCompteVerfiee(),
        ];

        return $this->render('banque/dashboard.html.twig', [
            'banque' => $banque,
            'compte_statut' => $compteStatut,
            'documents_stats' => $documentsStats,
            'messages_non_lus' => $messagesNonLus,
            'documents_recents' => $documentsRecents,
        ]);
    }
}
