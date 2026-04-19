<?php

declare(strict_types=1);

namespace App\Controller\Agriculteur;

use App\Entity\Utilisateur;
use App\Repository\AchatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur')]
#[IsGranted('ROLE_AGRICULTEUR')]
class ApplicationHistoryController extends AbstractController
{
    #[Route('/dossiers', name: 'agriculteur_application_history', methods: ['GET'])]
    public function index(AchatRepository $achatRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Utilisateur non autorise.');
        }

        $achats = $achatRepository->findByUtilisateur($user->getId() ?? 0);

        return $this->render('agriculteur/dossiers.html.twig', [
            'achats' => $achats,
        ]);
    }
}
