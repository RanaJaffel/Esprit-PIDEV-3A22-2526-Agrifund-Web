<?php

namespace App\Controller\Admin;

use App\Entity\Banque;
use App\Repository\BanqueRepository;
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

        $banquesQuery = $banqueRepository->searchBanques($search, $status);

        $pagination = $paginator->paginate(
            $banquesQuery,
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
        EntityManagerInterface $em,
        EmailService $emailService
    ): Response {
        if ($this->isCsrfTokenValid('verify' . $banque->getId(), $request->request->get('_token'))) {
            $action = $request->request->get('action');

            if ($action === 'approve') {
                $banque->setStatusCompte('actif');
                $banque->setCompteVerfiee(true);
                $em->flush();

                try {
                    $emailService->sendBanqueApprovalEmail($banque);
                    $this->addFlash('success', 'Compte banque approuvé et email envoyé avec succès !');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Compte approuvé mais erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
                }

            } elseif ($action === 'reject') {
                $banque->setStatusCompte('refuse');
                $banque->setCompteVerfiee(false);
                $em->flush();

                try {
                    $emailService->sendBanqueRejectionEmail($banque);
                    $this->addFlash('success', 'Compte banque refusé et email envoyé.');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Compte refusé mais erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
                }
            }
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

    #[Route('/{id}/pdf', name: 'admin_banques_pdf')]
    public function downloadPdf(Banque $banque): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = $this->renderView('admin/banques/pdf.html.twig', [
            'banque' => $banque,
            'date' => new \DateTime(),
            'reference' => 'BNK-' . str_pad($banque->getId(), 6, '0', STR_PAD_LEFT),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'banque_' . $banque->getId() . '_' . date('Ymd') . '.pdf';

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