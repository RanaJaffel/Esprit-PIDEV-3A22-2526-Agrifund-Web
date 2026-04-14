<?php

namespace App\Controller\Agriculteur;

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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur/project-agricole', name: 'agriculteur_project_agricole_')]
#[IsGranted('ROLE_AGRICULTEUR')]
class ProjectAgricoleController extends AbstractController
{
    #[Route('/page', name: 'page', methods: ['GET'])]
    public function page(): Response
    {
        $agriculteur = $this->getUser()?->getAgriculteur();

        if (!$agriculteur) {
            return $this->redirectToRoute('agriculteur_profile_edit');
        }

        return $this->render('agriculteur/project_agricole/page.html.twig');
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ProjectAgricoleRepository $repo): Response
    {
        $agriculteur = $this->getUser()?->getAgriculteur();

        if (!$agriculteur) {
            return $this->redirectToRoute('agriculteur_profile_edit');
        }

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
    public function exportPdf(ProjectAgricoleRepository $repo): Response
    {
        $agriculteur = $this->getUser()?->getAgriculteur();

        if (!$agriculteur) {
            return $this->redirectToRoute('agriculteur_profile_edit');
        }

        $projects = $repo->createQueryBuilder('p')
            ->where('p.agriculteur = :agriculteur')
            ->setParameter('agriculteur', $agriculteur)
            ->orderBy('p.datesoumission', 'DESC')
            ->getQuery()
            ->getResult();

        $totalBudget   = array_sum(array_map(fn($p) => (float) $p->getBudgetdemande(), $projects));
        $totalSurface  = array_sum(array_map(fn($p) => $p->getSurface(), $projects));

        $countApprouve = count(array_filter($projects, fn($p) => $p->getStatut() === 'Accepté'));
        $countEncours  = count(array_filter($projects, fn($p) => $p->getStatut() === 'En cours'));
        $countRefuse   = count(array_filter($projects, fn($p) => $p->getStatut() === 'Refusé'));
        $countTermine  = count(array_filter($projects, fn($p) => $p->getStatut() === 'Terminé'));

        $html = $this->renderView('agriculteur/project_agricole/export_pdf.html.twig', [
            'projects'      => $projects,
            'totalBudget'   => $totalBudget,
            'totalSurface'  => $totalSurface,
            'totalCount'    => count($projects),
            'countApprouve' => $countApprouve,
            'countEncours'  => $countEncours,
            'countRefuse'   => $countRefuse,
            'countTermine'  => $countTermine,
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

        return new Response($pdfContent, 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $agriculteur = $this->getUser()?->getAgriculteur();

        if (!$agriculteur) {
            return $this->redirectToRoute('agriculteur_profile_edit');
        }

        $project = new ProjectAgricole();

        $form = $this->createForm(ProjectAgricoleAgriculteurType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project->setAgriculteur($agriculteur);

            $em->persist($project);
            $em->flush();

            $this->addFlash('success', '✅ Projet ajouté avec succès.');
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        return $this->render('agriculteur/project_agricole/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id, ProjectAgricoleRepository $repo): Response
    {
        $project = $this->findOwnProject($id, $repo);

        if (!$project) {
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        return $this->render('agriculteur/project_agricole/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, ProjectAgricoleRepository $repo): Response
    {
        $project = $this->findOwnProject($id, $repo);

        if (!$project) {
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        $form = $this->createForm(ProjectAgricoleAgriculteurType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', '✏️ Projet modifié avec succès.');
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        return $this->render('agriculteur/project_agricole/edit.html.twig', [
            'project' => $project,
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em, ProjectAgricoleRepository $repo): Response
    {
        $project = $this->findOwnProject($id, $repo);

        if (!$project) {
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        if ($this->isCsrfTokenValid('delete_project_' . $id, $request->request->get('_token'))) {
            $em->remove($project);
            $em->flush();

            $this->addFlash('success', '🗑️ Projet supprimé.');
        }

        return $this->redirectToRoute('agriculteur_project_agricole_index');
    }

    private function findOwnProject(int $id, ProjectAgricoleRepository $repo): ?ProjectAgricole
    {
        $agriculteur = $this->getUser()?->getAgriculteur();
        $project     = $repo->find($id);

        if (!$project || $project->getAgriculteur()?->getId() !== $agriculteur?->getId()) {
            return null;
        }

        return $project;
    }
}
