<?php

namespace App\Controller\Agriculteur;

use App\Repository\CapteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur')]
#[IsGranted('ROLE_AGRICULTEUR')]
class AgriculteurPagesController extends AbstractController
{
    #[Route('/pages', name: 'agriculteur_pages', methods: ['GET'])]
    public function index(CapteurRepository $capteurRepository): Response
    {
        $user = $this->getUser();
        $projects = [];

        if ($user && method_exists($user, 'getId')) {
            foreach ($capteurRepository->findDistinctProjectsByUser((int) $user->getId()) as $projectId) {
                $projects[] = [
                    'idproject' => $projectId,
                    'sensorCount' => count($capteurRepository->findByProject($projectId)),
                    'activeCount' => $capteurRepository->countActifsByProject($projectId),
                ];
            }
        }

        return $this->render('agriculteur/pages.html.twig', [
            'projects' => $projects,
        ]);
    }
}
