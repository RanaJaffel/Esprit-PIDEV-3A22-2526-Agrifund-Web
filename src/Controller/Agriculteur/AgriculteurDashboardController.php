<?php

namespace App\Controller\Agriculteur;

use App\Repository\DocumentRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur')]
#[IsGranted('ROLE_AGRICULTEUR')]
class AgriculteurDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'agriculteur_dashboard')]
    public function index(
        DocumentRepository $documentRepository,
        MessageRepository $messageRepository
    ): Response {
        $currentUser = $this->getUser();
        $agriculteur = $currentUser->getAgriculteur();

        // Statistiques documents
        $documents = $documentRepository->findByUtilisateur($currentUser);
        $documentsStats = [
            'total' => count($documents),
            'valides' => count(array_filter($documents, fn($d) => $d->getStatut() === 'valide')),
            'en_attente' => count(array_filter($documents, fn($d) => $d->getStatut() === 'en_attente')),
            'rejetes' => count(array_filter($documents, fn($d) => $d->getStatut() === 'rejete')),
        ];

        // Messages non lus
        $messagesNonLus = $messageRepository->countAllUnreadMessages($currentUser->getId());

        // Statut du compte
        $compteStatut = [
            'status' => $agriculteur->getStatuscompte(),
            'verifie' => $agriculteur->isCompteverifie(),
        ];

        return $this->render('agriculteur/dashboard.html.twig', [
            'agriculteur' => $agriculteur,
            'compte_statut' => $compteStatut,
            'documents_stats' => $documentsStats,
            'messages_non_lus' => $messagesNonLus,
            'documents_recents' => array_slice($documents, 0, 5),
        ]);
    }
}