<?php

namespace App\Controller;

use App\Form\RapportJournalierGenerateType;
use App\Repository\CapteurRepository;
use App\Repository\RapportJournalierRepository;
use App\Service\RapportJournalierGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RapportJournalierController extends AbstractController
{
    private function getUserIdOrDeny(): int
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException();
        }
        return (int)$user->getId();
    }

    private function denyIfProjectNotOwned(int $idproject, CapteurRepository $capteurRepo): void
    {
        $userId = $this->getUserIdOrDeny();

        $count = $capteurRepo->count([
            'idproject' => $idproject,
            'idUser' => $userId,
        ]);

        if ($count === 0) {
            throw $this->createAccessDeniedException("Accès refusé : ce projet ne vous appartient pas.");
        }
    }

    // ====================== AGRI HOME ======================
    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/rapports-journaliers', name: 'agriculteur_rapport_journalier_home')]
    public function home(CapteurRepository $capteurRepo): Response
    {
        $userId = $this->getUserIdOrDeny();
        $projects = $capteurRepo->findDistinctProjectsByUser($userId);

        if (!$projects) {
            $this->addFlash('danger', "Aucun projet n'est lié à vos capteurs.");
            return $this->render('agriculteur/rapport_journalier/choose_project.html.twig', [
                'projects' => [],
            ]);
        }

        if (count($projects) === 1) {
            return $this->redirectToRoute('agriculteur_rapport_journalier', [
                'idproject' => $projects[0],
            ]);
        }

        return $this->render('agriculteur/rapport_journalier/choose_project.html.twig', [
            'projects' => $projects,
        ]);
    }

    // ====================== AGRI LISTE ======================
    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/rapports-journaliers/{idproject}', name: 'agriculteur_rapport_journalier')]
    public function index(
        int $idproject,
        Request $request,
        CapteurRepository $capteurRepo,
        RapportJournalierRepository $repo
    ): Response {
        $this->denyIfProjectNotOwned($idproject, $capteurRepo);

        // date safe
        $dateStr = (string)$request->query->get('date', (new \DateTimeImmutable('today'))->format('Y-m-d'));
        try {
            $date = new \DateTimeImmutable($dateStr);
        } catch (\Exception) {
            $date = new \DateTimeImmutable('today');
        }

        $typeMesure = (string)$request->query->get('type', '');

        // ✅ Compatible avec ton repo : on choisit la méthode selon filtre type
        if ($typeMesure !== '') {
            $rapports = $repo->findByProjectDateAndType($idproject, $date, $typeMesure);
        } else {
            $rapports = $repo->findByProjectAndDate($idproject, $date);
        }

        $types = $repo->findTypesByProject($idproject);

        $stats = [
            'total' => count($rapports),
            'alerts' => count(array_filter($rapports, fn($r) => $r->getConditionDominante() !== 'NORMAL')),
            'types' => count($types),
        ];

        $generateForm = $this->createForm(RapportJournalierGenerateType::class, [
            'date' => $date,
        ], [
            'method' => 'POST',
            'action' => $this->generateUrl('agriculteur_rapport_journalier_generate', ['idproject' => $idproject]),
        ]);

        return $this->render('agriculteur/rapport_journalier/index.html.twig', [
            'idproject' => $idproject,
            'date' => $date,
            'typeMesure' => $typeMesure,
            'types' => $types,
            'rapports' => $rapports,
            'stats' => $stats,
            'generateForm' => $generateForm->createView(),
        ]);
    }

    // ====================== AGRI GENERATE ======================
    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/rapports-journaliers/{idproject}/generate', name: 'agriculteur_rapport_journalier_generate', methods: ['POST'])]
    public function generate(
        int $idproject,
        Request $request,
        CapteurRepository $capteurRepo,
        RapportJournalierGenerator $generator
    ): Response {
        $this->denyIfProjectNotOwned($idproject, $capteurRepo);

        $form = $this->createForm(RapportJournalierGenerateType::class);
        $form->handleRequest($request);

        $date = new \DateTimeImmutable('today');
        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date')->getData();
        }

        $nb = $generator->generateForProjectDate($idproject, $date);
        $this->addFlash('success', "Rapport généré : {$nb} ligne(s).");

        return $this->redirectToRoute('agriculteur_rapport_journalier', [
            'idproject' => $idproject,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    // ====================== ADMIN LISTE ======================
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/rapports-journaliers', name: 'admin_rapport_journalier')]
    public function adminIndex(Request $request, RapportJournalierRepository $repo): Response
    {
        $idproject = $request->query->get('projet');
        $idproject = ($idproject !== null && $idproject !== '') ? (int)$idproject : null;

        $date = $request->query->get('date') ? new \DateTimeImmutable($request->query->get('date')) : null;
        $typeMesure = $request->query->get('type') ?: null;

        $rapports = $repo->findAllByFilters($idproject, $date, $typeMesure);
        $types = $repo->findAllTypes();

        $stats = [
            'total' => count($rapports),
            'projects' => count(array_unique(array_map(fn($r) => $r->getIdproject(), $rapports))),
            'alerts' => count(array_filter($rapports, fn($r) => $r->getConditionDominante() !== 'NORMAL')),
            'types' => count(array_unique(array_map(fn($r) => $r->getTypeMesure(), $rapports))),
        ];

        // ✅ un seul form, mais en mode admin (avec idproject)
        $generateForm = $this->createForm(RapportJournalierGenerateType::class, [
            'idproject' => $idproject ?? 1,
            'date' => new \DateTimeImmutable('today'),
        ], [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_rapport_journalier_generate'),
            'for_admin' => true,
        ]);

        return $this->render('admin/rapport_journalier/index.html.twig', [
            'rapports' => $rapports,
            'types' => $types,
            'stats' => $stats,
            'idproject' => $idproject,
            'date' => $date,
            'typeMesure' => $typeMesure,
            'generateForm' => $generateForm->createView(),
        ]);
    }

    // ====================== ADMIN GENERATE ======================
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/rapports-journaliers/generate', name: 'admin_rapport_journalier_generate', methods: ['POST'])]
    public function adminGenerate(Request $request, RapportJournalierGenerator $generator): Response
    {
        $form = $this->createForm(RapportJournalierGenerateType::class, null, [
            'for_admin' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $idproject = (int)$form->get('idproject')->getData();
            $date = $form->get('date')->getData();

            $nb = $generator->generateForProjectDate($idproject, $date);
            $this->addFlash('success', "Admin: rapport généré ({$nb} ligne(s)) pour projet #{$idproject}.");
        } else {
            $this->addFlash('danger', 'Formulaire invalide.');
        }

        return $this->redirectToRoute('admin_rapport_journalier');
    }
}