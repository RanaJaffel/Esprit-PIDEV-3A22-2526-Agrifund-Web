<?php

namespace App\Controller\Admin;

use App\Entity\Agriculteur;
use App\Repository\AgriculteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/agriculteurs')]
#[IsGranted('ROLE_ADMIN')]
class AdminAgriculteurController extends AbstractController
{
    #[Route('/', name: 'admin_agriculteurs_index')]
    public function index(
        Request $request,
        AgriculteurRepository $agriculteurRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search');
        $status = $request->query->get('status');

        $agriculteurs = $agriculteurRepository->searchAgriculteurs($search, $status);

        $pagination = $paginator->paginate(
            $agriculteurs,
            $request->query->getInt('page', 1),
            10
        );

        $statistics = $agriculteurRepository->getStatistics();

        return $this->render('admin/agriculteurs/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
            'status' => $status,
            'statistics' => $statistics,
        ]);
    }

    #[Route('/{id}', name: 'admin_agriculteurs_show')]
    public function show(Agriculteur $agriculteur): Response
    {
        return $this->render('admin/agriculteurs/show.html.twig', [
            'agriculteur' => $agriculteur,
        ]);
    }

    #[Route('/{id}/verify', name: 'admin_agriculteurs_verify', methods: ['POST'])]
    public function verify(
        Agriculteur $agriculteur,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('verify' . $agriculteur->getId(), $request->request->get('_token'))) {
            $action = $request->request->get('action');

            if ($action === 'approve') {
                $agriculteur->setStatuscompte('actif');
                $agriculteur->setCompteverifie(true);
                $this->addFlash('success', 'Compte agriculteur approuvé avec succès !');
            } elseif ($action === 'reject') {
                $agriculteur->setStatuscompte('refuse');
                $agriculteur->setCompteverifie(false);
                $this->addFlash('success', 'Compte agriculteur refusé.');
            }

            $em->flush();
        }

        return $this->redirectToRoute('admin_agriculteurs_index');
    }

    #[Route('/{id}/toggle-status', name: 'admin_agriculteurs_toggle_status', methods: ['POST'])]
    public function toggleStatus(
        Agriculteur $agriculteur,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('toggle' . $agriculteur->getId(), $request->request->get('_token'))) {
            if ($agriculteur->getStatuscompte() === 'actif') {
                $agriculteur->setStatuscompte('suspendu');
                $this->addFlash('success', 'Compte suspendu.');
            } else {
                $agriculteur->setStatuscompte('actif');
                $this->addFlash('success', 'Compte activé.');
            }

            $em->flush();
        }

        return $this->redirectToRoute('admin_agriculteurs_index');
    }
}