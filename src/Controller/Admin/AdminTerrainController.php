<?php

namespace App\Controller\Admin;

use App\Repository\CapteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/terrain')]
#[IsGranted('ROLE_ADMIN')]
class AdminTerrainController extends AbstractController
{
    #[Route('/projects', name: 'admin_terrain_projects', methods: ['GET'])]
    public function projects(CapteurRepository $capteurRepository): Response
    {
        return $this->render('admin/terrain/projects.html.twig', [
            'projects' => $capteurRepository->findProjectSummaries(),
        ]);
    }
}
