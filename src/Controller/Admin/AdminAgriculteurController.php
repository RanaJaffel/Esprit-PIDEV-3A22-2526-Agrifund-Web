<?php

namespace App\Controller\Admin;

use App\Entity\Agriculteur;
use App\Repository\AgriculteurRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
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

        $agriculteursQuery = $agriculteurRepository->searchAgriculteurs($search, $status);

        $pagination = $paginator->paginate(
            $agriculteursQuery,
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
        EntityManagerInterface $em,
        EmailService $emailService
    ): Response {
        if ($this->isCsrfTokenValid('verify' . $agriculteur->getId(), $request->request->get('_token'))) {
            $action = $request->request->get('action');

            if ($action === 'approve') {
                $agriculteur->setStatuscompte('actif');
                $agriculteur->setCompteverifie(true);
                $em->flush();

                try {
                    $emailService->sendAgriculteurApprovalEmail($agriculteur);
                    $this->addFlash('success', 'Compte agriculteur approuvé et email envoyé avec succès !');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Compte approuvé mais erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
                }

            } elseif ($action === 'reject') {
                $agriculteur->setStatuscompte('refuse');
                $agriculteur->setCompteverifie(false);
                $em->flush();

                try {
                    $emailService->sendAgriculteurRejectionEmail($agriculteur);
                    $this->addFlash('success', 'Compte agriculteur refusé et email envoyé.');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Compte refusé mais erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
                }
            }
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

    #[Route('/{id}/pdf', name: 'admin_agriculteurs_pdf')]
    public function downloadPdf(Agriculteur $agriculteur): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = $this->renderView('admin/agriculteurs/pdf.html.twig', [
            'agriculteur' => $agriculteur,
            'date' => new \DateTime(),
            'reference' => 'AGR-' . str_pad($agriculteur->getId(), 6, '0', STR_PAD_LEFT),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'agriculteur_' . $agriculteur->getId() . '_' . date('Ymd') . '.pdf';

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}