<?php

namespace App\Controller\Admin;

use App\Entity\Banque;
use App\Repository\BanqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/banques')]
#[IsGranted('ROLE_ADMIN')]
class AdminBanqueController extends AbstractController
{
    #[Route('/', name: 'admin_banques_index')]
    public function index(
        Request $request,
        BanqueRepository $banqueRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search');
        $status = $request->query->get('status');

        $banques = $banqueRepository->searchBanques($search, $status);

        $pagination = $paginator->paginate(
            $banques,
            $request->query->getInt('page', 1),
            10
        );

        $statistics = $banqueRepository->getStatistics();

        return $this->render('admin/banques/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
            'status' => $status,
            'statistics' => $statistics,
        ]);
    }

    #[Route('/{id}', name: 'admin_banques_show')]
    public function show(Banque $banque): Response
    {
        return $this->render('admin/banques/show.html.twig', [
            'banque' => $banque,
        ]);
    }

    #[Route('/{id}/verify', name: 'admin_banques_verify', methods: ['POST'])]
    public function verify(
        Banque $banque,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('verify' . $banque->getId(), $request->request->get('_token'))) {
            $action = $request->request->get('action');

            if ($action === 'approve') {
                $banque->setStatusCompte('actif');
                $banque->setCompteVerfiee(true);
                $this->addFlash('success', 'Compte banque approuvé avec succès !');
            } elseif ($action === 'reject') {
                $banque->setStatusCompte('refuse');
                $banque->setCompteVerfiee(false);
                $this->addFlash('success', 'Compte banque refusé.');
            }

            $em->flush();
        }

        return $this->redirectToRoute('admin_banques_index');
    }

    #[Route('/{id}/toggle-status', name: 'admin_banques_toggle_status', methods: ['POST'])]
    public function toggleStatus(
        Banque $banque,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('toggle' . $banque->getId(), $request->request->get('_token'))) {
            if ($banque->getStatusCompte() === 'actif') {
                $banque->setStatusCompte('suspendu');
                $this->addFlash('success', 'Compte suspendu.');
            } else {
                $banque->setStatusCompte('actif');
                $this->addFlash('success', 'Compte activé.');
            }

            $em->flush();
        }

        return $this->redirectToRoute('admin_banques_index');
    }
}