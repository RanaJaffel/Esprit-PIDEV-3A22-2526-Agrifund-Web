<?php

namespace App\Controller\Agriculteur;

use App\Entity\Agriculteur;
use App\Entity\RessourceProject;
use App\Form\RessourceProjectAgriculteurType;
use App\Repository\RessourceProjectRepository;
use App\Repository\ProjectAgricoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agriculteur/ressource-project', name: 'agriculteur_ressource_project_')]
class RessourceProjectController extends AbstractController
{
    private function getAgriculteur(Request $request, EntityManagerInterface $em): ?Agriculteur
    {
        $id = (int) $request->getSession()->get('agriculteur_id', 0);
        return $id > 0 ? $em->getRepository(Agriculteur::class)->find($id) : null;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        RessourceProjectRepository $repo,
        ProjectAgricoleRepository $projectRepo,
        EntityManagerInterface $em
    ): Response {
        $agriculteur   = $this->getAgriculteur($request, $em);
        $search        = $request->query->get('search', '');
        $typeFilter    = $request->query->get('type', '');
        $statutFilter  = $request->query->get('statut', '');
        $projectFilter = $request->query->getInt('project', 0);

        $myProjects = $projectRepo->createQueryBuilder('p')
            ->where('p.agriculteur = :agriculteur')
            ->setParameter('agriculteur', $agriculteur)
            ->getQuery()->getResult();

        $myProjectIds = array_map(fn($p) => $p->getIdproject(), $myProjects);

        $ressources = [];
        if (!empty($myProjectIds)) {
            $qb = $repo->createQueryBuilder('r')
                ->where('r.project IN (:ids)')
                ->setParameter('ids', $myProjectIds)
                ->orderBy('r.dateajout', 'DESC');

            if ($search !== '') {
                $qb->andWhere('r.nomressource LIKE :search OR r.fournisseur LIKE :search')
                   ->setParameter('search', '%' . $search . '%');
            }
            if ($typeFilter !== '') {
                $qb->andWhere('r.typeressource = :type')
                   ->setParameter('type', $typeFilter);
            }
            if ($statutFilter !== '') {
                $qb->andWhere('r.statut = :statut')
                   ->setParameter('statut', $statutFilter);
            }
            if ($projectFilter > 0) {
                $qb->andWhere('r.project = :project')
                   ->setParameter('project', $projectFilter);
            }

            $ressources = $qb->getQuery()->getResult();
        }

        return $this->render('agriculteur/ressource_project/index.html.twig', [
            'ressources'    => $ressources,
            'projects'      => $myProjects,
            'search'        => $search,
            'typeFilter'    => $typeFilter,
            'statutFilter'  => $statutFilter,
            'projectFilter' => $projectFilter,
        ]);
    }

    #[Route('/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(
        Request $request,
        RessourceProjectRepository $repo,
        ProjectAgricoleRepository $projectRepo,
        EntityManagerInterface $em
    ): Response {
        $agriculteur = $this->getAgriculteur($request, $em);

        $myProjects = $projectRepo->createQueryBuilder('p')
            ->where('p.agriculteur = :agriculteur')
            ->setParameter('agriculteur', $agriculteur)
            ->getQuery()->getResult();

        $myProjectIds = array_map(fn($p) => $p->getIdproject(), $myProjects);

        $ressources = [];
        if (!empty($myProjectIds)) {
            $ressources = $repo->createQueryBuilder('r')
                ->where('r.project IN (:ids)')
                ->setParameter('ids', $myProjectIds)
                ->orderBy('r.dateajout', 'DESC')
                ->getQuery()->getResult();
        }

        $totalRessources = count($ressources);
        $countEquipement = count(array_filter($ressources, fn($r) => $r->getTyperessource() === 'equipement'));
        $countMateriaux  = count(array_filter($ressources, fn($r) => $r->getTyperessource() === 'materiaux'));
        $countService    = count(array_filter($ressources, fn($r) => $r->getTyperessource() === 'service'));
        $totalCout       = array_sum(array_map(fn($r) => (float) $r->getCout() * $r->getQuantite(), $ressources));

        $html = $this->renderView('agriculteur/ressource_project/export_pdf.html.twig', [
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

        $filename   = 'MesRessources_' . date('Y-m-d_H-i-s') . '.pdf';
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
    public function new(Request $request, EntityManagerInterface $em, ProjectAgricoleRepository $projectRepo): Response
    {
        $agriculteur = $this->getAgriculteur($request, $em);

        $myProjects = $projectRepo->createQueryBuilder('p')
            ->where('p.agriculteur = :agriculteur')
            ->setParameter('agriculteur', $agriculteur)
            ->getQuery()->getResult();

        $ressource = new RessourceProject();
        $form = $this->createForm(RessourceProjectAgriculteurType::class, $ressource, [
            'projects' => $myProjects,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ressource);
            $em->flush();
            $this->addFlash('success', '✅ Ressource "' . $ressource->getNomressource() . '" ajoutée.');
            return $this->redirectToRoute('agriculteur_ressource_project_index');
        }

        return $this->render('agriculteur/ressource_project/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request, RessourceProjectRepository $repo, EntityManagerInterface $em): Response
    {
        $ressource = $this->findOwnRessource($id, $request, $repo, $em);
        if (!$ressource) {
            return $this->redirectToRoute('agriculteur_ressource_project_index');
        }
        return $this->render('agriculteur/ressource_project/show.html.twig', ['ressource' => $ressource]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        RessourceProjectRepository $repo,
        ProjectAgricoleRepository $projectRepo
    ): Response {
        $ressource = $this->findOwnRessource($id, $request, $repo, $em);
        if (!$ressource) {
            return $this->redirectToRoute('agriculteur_ressource_project_index');
        }

        $agriculteur = $this->getAgriculteur($request, $em);
        $myProjects = $projectRepo->createQueryBuilder('p')
            ->where('p.agriculteur = :agriculteur')
            ->setParameter('agriculteur', $agriculteur)
            ->getQuery()->getResult();

        $form = $this->createForm(RessourceProjectAgriculteurType::class, $ressource, [
            'projects' => $myProjects,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✏️ Ressource "' . $ressource->getNomressource() . '" modifiée.');
            return $this->redirectToRoute('agriculteur_ressource_project_index');
        }

        return $this->render('agriculteur/ressource_project/edit.html.twig', [
            'ressource' => $ressource,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em, RessourceProjectRepository $repo): Response
    {
        $ressource = $this->findOwnRessource($id, $request, $repo, $em);
        if (!$ressource) {
            return $this->redirectToRoute('agriculteur_ressource_project_index');
        }

        if ($this->isCsrfTokenValid('delete_ressource_' . $id, $request->request->get('_token'))) {
            $nom = $ressource->getNomressource();
            $em->remove($ressource);
            $em->flush();
            $this->addFlash('success', '🗑️ Ressource "' . $nom . '" supprimée.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('agriculteur_ressource_project_index');
    }

    private function findOwnRessource(int $id, Request $request, RessourceProjectRepository $repo, EntityManagerInterface $em): ?RessourceProject
    {
        $agriculteur = $this->getAgriculteur($request, $em);
        $ressource   = $repo->find($id);

        if (!$ressource || $ressource->getProject()?->getAgriculteur()?->getId() !== $agriculteur?->getId()) {
            $this->addFlash('danger', 'Ressource introuvable ou accès refusé.');
            return null;
        }

        return $ressource;
    }
}