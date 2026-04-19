<?php

namespace App\Controller\Admin;

use App\Repository\ProjectAgricoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin/project-agricole', name: 'admin_project_agricole_')]
class ProjectAgricoleController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        ProjectAgricoleRepository $repo,
        ChartBuilderInterface $chartBuilder
    ): Response
    {
        $search = $request->query->get('search', '');
        $statut = $request->query->get('statut', '');

        $qb = $repo->createQueryBuilder('p')->orderBy('p.datesoumission', 'DESC');

        if ($search !== '') {
            $qb->andWhere('p.nomproject LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        if ($statut !== '') {
            $qb->andWhere('p.statut = :statut')
               ->setParameter('statut', $statut);
        }

        $projects    = $qb->getQuery()->getResult();
        $allProjects = $repo->findAll();

        $totalBudget   = array_sum(array_map(fn($p) => (float) $p->getBudgetdemande(), $allProjects));
        $totalSurface  = array_sum(array_map(fn($p) => $p->getSurface(), $allProjects));
        $countEncours  = count(array_filter($allProjects, fn($p) => $p->getStatut() === 'en cours'));
        $countApprouve = count(array_filter($allProjects, fn($p) => $p->getStatut() === 'accepte'));
        $countRefuse   = count(array_filter($allProjects, fn($p) => $p->getStatut() === 'refuse'));

        $statusChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $statusChart->setData([
            'labels' => ['Acceptés', 'Refusés', 'En cours'],
            'datasets' => [[
                'label' => 'Statut des projets',
                'data' => [$countApprouve, $countRefuse, $countEncours],
                'backgroundColor' => ['#16a34a', '#dc2626', '#ea580c'],
                'borderColor' => ['#ffffff', '#ffffff', '#ffffff'],
                'borderWidth' => 2,
                'hoverOffset' => 8,
            ]],
        ]);
        $statusChart->setOptions([
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 14,
                        'padding' => 16,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
            'cutout' => '62%',
        ]);

        return $this->render('admin/project_agricole/index.html.twig', [
            'projects'      => $projects,
            'search'        => $search,
            'statut'        => $statut,
            'totalBudget'   => $totalBudget,
            'totalSurface'  => $totalSurface,
            'countEncours'  => $countEncours,
            'countApprouve' => $countApprouve,
            'countRefuse'   => $countRefuse,
            'totalCount'    => count($allProjects),
            'statusChart'   => $statusChart,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, ProjectAgricoleRepository $repo): Response
    {
        $project = $repo->find($id);
        if (!$project) {
            $this->addFlash('danger', 'Projet introuvable.');
            return $this->redirectToRoute('admin_project_agricole_index');
        }

        return $this->render('admin/project_agricole/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em, ProjectAgricoleRepository $repo): Response
    {
        $project = $repo->find($id);
        if (!$project) {
            $this->addFlash('danger', 'Projet introuvable.');
            return $this->redirectToRoute('admin_project_agricole_index');
        }

        if ($this->isCsrfTokenValid('delete_project_' . $id, $request->request->get('_token'))) {
            $nom = $project->getNomproject();
            $project->getRessources()->toArray();
            $em->remove($project);
            $em->flush();
            $this->addFlash('success', '🗑️ Projet "' . $nom . '" et ses ressources supprimés.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide. Suppression annulée.');
        }

        return $this->redirectToRoute('admin_project_agricole_index');
    }

    #[Route('/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(ProjectAgricoleRepository $repo): Response
    {
        $projects = $repo->findAll();

        $totalBudget   = array_sum(array_map(fn($p) => (float) $p->getBudgetdemande(), $projects));
        $totalSurface  = array_sum(array_map(fn($p) => $p->getSurface(), $projects));
        $countEncours  = count(array_filter($projects, fn($p) => $p->getStatut() === 'en cours'));
        $countApprouve = count(array_filter($projects, fn($p) => $p->getStatut() === 'accepte'));
        $countRefuse   = count(array_filter($projects, fn($p) => $p->getStatut() === 'refuse'));

        $html = $this->renderView('admin/project_agricole/export_pdf.html.twig', [
            'projects'      => $projects,
            'totalBudget'   => $totalBudget,
            'totalSurface'  => $totalSurface,
            'countEncours'  => $countEncours,
            'countApprouve' => $countApprouve,
            'countRefuse'   => $countRefuse,
            'totalCount'    => count($projects),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename   = 'Projets_Agricoles_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdfContent = $dompdf->output();

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-Length', strlen($pdfContent));
        $response->headers->set('Cache-Control', 'public, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');

        return $response;
    }
}
