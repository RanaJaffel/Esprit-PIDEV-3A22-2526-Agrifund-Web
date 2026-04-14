<?php

namespace App\Controller\Admin;

use App\Repository\RessourceProjectRepository;
use App\Repository\ProjectAgricoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/ressource-project', name: 'admin_ressource_project_')]
class RessourceProjectController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        RessourceProjectRepository $repo,
        ProjectAgricoleRepository  $projectRepo
    ): Response {
        $search        = $request->query->get('search', '');
        $typeFilter    = $request->query->get('type', '');
        $projectFilter = $request->query->getInt('project', 0);

        $qb = $repo->createQueryBuilder('r')
                   ->leftJoin('r.project', 'p')
                   ->orderBy('r.dateajout', 'DESC');

        if ($search !== '') {
            $qb->andWhere('r.nomressource LIKE :search OR r.fournisseur LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        if ($typeFilter !== '') {
            $qb->andWhere('r.typeressource = :type')
               ->setParameter('type', $typeFilter);
        }
        if ($projectFilter > 0) {
            $qb->andWhere('r.project = :project')
               ->setParameter('project', $projectFilter);
        }

        $ressources = $qb->getQuery()->getResult();

        $all             = $repo->findAll();
        $totalRessources = count($all);
        $countEquipement = count(array_filter($all, fn($r) => $r->getTyperessource() === 'equipement'));
        $countMateriaux  = count(array_filter($all, fn($r) => $r->getTyperessource() === 'materiaux'));
        $countService    = count(array_filter($all, fn($r) => $r->getTyperessource() === 'service'));
        $totalCout       = array_sum(array_map(fn($r) => (float) $r->getCout() * $r->getQuantite(), $all));

        return $this->render('admin/ressource_project/index.html.twig', [
            'ressources'      => $ressources,
            'projects'        => $projectRepo->findAllOrderedByDate(),
            'search'          => $search,
            'typeFilter'      => $typeFilter,
            'projectFilter'   => $projectFilter,
            'totalRessources' => $totalRessources,
            'countEquipement' => $countEquipement,
            'countMateriaux'  => $countMateriaux,
            'countService'    => $countService,
            'totalCout'       => $totalCout,
        ]);
    }

    #[Route('/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(RessourceProjectRepository $repo): Response
    {
        $ressources = $repo->findAll();

        $totalRessources = count($ressources);
        $countEquipement = count(array_filter($ressources, fn($r) => $r->getTyperessource() === 'equipement'));
        $countMateriaux  = count(array_filter($ressources, fn($r) => $r->getTyperessource() === 'materiaux'));
        $countService    = count(array_filter($ressources, fn($r) => $r->getTyperessource() === 'service'));
        $totalCout       = array_sum(array_map(fn($r) => (float) $r->getCout() * $r->getQuantite(), $ressources));

        $html = $this->renderView('admin/ressource_project/export_pdf.html.twig', [
            'ressources'      => $ressources,
            'totalRessources' => $totalRessources,
            'countEquipement' => $countEquipement,
            'countMateriaux'  => $countMateriaux,
            'countService'    => $countService,
            'totalCout'       => $totalCout,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename   = 'Ressources_' . date('Y-m-d_H-i-s') . '.pdf';
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

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, RessourceProjectRepository $repo): Response
    {
        $ressource = $repo->find($id);
        if (!$ressource) {
            $this->addFlash('danger', 'Ressource introuvable.');
            return $this->redirectToRoute('admin_ressource_project_index');
        }

        return $this->render('admin/ressource_project/show.html.twig', [
            'ressource' => $ressource,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        RessourceProjectRepository $repo
    ): Response {
        $ressource = $repo->find($id);
        if (!$ressource) {
            $this->addFlash('danger', 'Ressource introuvable.');
            return $this->redirectToRoute('admin_ressource_project_index');
        }

        if ($this->isCsrfTokenValid('delete_ressource_' . $id, $request->request->get('_token'))) {
            $nom = $ressource->getNomressource();
            $em->remove($ressource);
            $em->flush();
            $this->addFlash('success', '🗑️ Ressource "' . $nom . '" supprimée.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_ressource_project_index');
    }
}