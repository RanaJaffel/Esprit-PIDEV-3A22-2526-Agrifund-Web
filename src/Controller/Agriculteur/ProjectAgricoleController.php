<?php

namespace App\Controller\Agriculteur;

use App\Entity\Agriculteur;
use App\Entity\ProjectAgricole;
use App\Form\ProjectAgricoleAgriculteurType;
use App\Repository\ProjectAgricoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agriculteur/project-agricole', name: 'agriculteur_project_agricole_')]
class ProjectAgricoleController extends AbstractController
{
    private function getAgriculteur(Request $request, EntityManagerInterface $em): ?Agriculteur
    {
        $id = (int) $request->getSession()->get('agriculteur_id', 0);
        return $id > 0 ? $em->getRepository(Agriculteur::class)->find($id) : null;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, ProjectAgricoleRepository $repo, EntityManagerInterface $em): Response
    {
        $agriculteur = $this->getAgriculteur($request, $em);

        $projects = $repo->createQueryBuilder('p')
            ->where('p.agriculteur = :agriculteur')
            ->setParameter('agriculteur', $agriculteur)
            ->orderBy('p.datesoumission', 'DESC')
            ->getQuery()
            ->getResult();

        $totalBudget  = array_sum(array_map(fn($p) => (float) $p->getBudgetdemande(), $projects));
        $totalSurface = array_sum(array_map(fn($p) => $p->getSurface(), $projects));

        return $this->render('agriculteur/project_agricole/index.html.twig', [
            'projects'     => $projects,
            'totalBudget'  => $totalBudget,
            'totalSurface' => $totalSurface,
        ]);
    }

    #[Route('/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request, ProjectAgricoleRepository $repo, EntityManagerInterface $em): Response
    {
        $agriculteur = $this->getAgriculteur($request, $em);

        $projects = $repo->createQueryBuilder('p')
            ->where('p.agriculteur = :agriculteur')
            ->setParameter('agriculteur', $agriculteur)
            ->orderBy('p.datesoumission', 'DESC')
            ->getQuery()
            ->getResult();

        $totalBudget   = array_sum(array_map(fn($p) => (float) $p->getBudgetdemande(), $projects));
        $totalSurface  = array_sum(array_map(fn($p) => $p->getSurface(), $projects));
        $countApprouve = count(array_filter($projects, fn($p) => $p->getStatut() === 'accepte'));
        $countEncours  = count(array_filter($projects, fn($p) => $p->getStatut() === 'en cours'));
        $countRefuse   = count(array_filter($projects, fn($p) => $p->getStatut() === 'refuse'));

        $html = $this->renderView('agriculteur/project_agricole/export_pdf.html.twig', [
            'projects'      => $projects,
            'totalBudget'   => $totalBudget,
            'totalSurface'  => $totalSurface,
            'totalCount'    => count($projects),
            'countApprouve' => $countApprouve,
            'countEncours'  => $countEncours,
            'countRefuse'   => $countRefuse,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename   = 'MesProjets_' . date('Y-m-d_H-i-s') . '.pdf';
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

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $agriculteur = $this->getAgriculteur($request, $em);

        $project = new ProjectAgricole();
        $project->setStatut('en cours');
        $project->setAgriculteur($agriculteur);

        $form = $this->createForm(ProjectAgricoleAgriculteurType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();
            $this->addFlash('success', '✅ Votre projet "' . $project->getNomproject() . '" a été soumis.');
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        return $this->render('agriculteur/project_agricole/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request, ProjectAgricoleRepository $repo, EntityManagerInterface $em): Response
    {
        $project = $this->findOwnProject($id, $request, $repo, $em);
        if (!$project) {
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }
        return $this->render('agriculteur/project_agricole/show.html.twig', ['project' => $project]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, ProjectAgricoleRepository $repo): Response
    {
        $project = $this->findOwnProject($id, $request, $repo, $em);
        if (!$project) {
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        $form = $this->createForm(ProjectAgricoleAgriculteurType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✏️ Projet "' . $project->getNomproject() . '" modifié.');
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        return $this->render('agriculteur/project_agricole/edit.html.twig', [
            'project' => $project,
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em, ProjectAgricoleRepository $repo): Response
    {
        $project = $this->findOwnProject($id, $request, $repo, $em);
        if (!$project) {
            return $this->redirectToRoute('agriculteur_project_agricole_index');
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

        return $this->redirectToRoute('agriculteur_project_agricole_index');
    }

    private function findOwnProject(int $id, Request $request, ProjectAgricoleRepository $repo, EntityManagerInterface $em): ?ProjectAgricole
    {
        $agriculteur = $this->getAgriculteur($request, $em);
        $project     = $repo->find($id);

        if (!$project || $project->getAgriculteur()?->getId() !== $agriculteur?->getId()) {
            $this->addFlash('danger', 'Projet introuvable ou accès refusé.');
            return null;
        }

        return $project;
    }
}