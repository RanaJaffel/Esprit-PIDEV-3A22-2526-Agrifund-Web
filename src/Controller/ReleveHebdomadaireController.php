<?php

namespace App\Controller;

use App\Form\ReleveHebdomadaireGenerateType;
use App\Repository\CapteurRepository;
use App\Repository\ReleveHebdomadaireRepository;
use App\Service\ReleveHebdomadaireGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReleveHebdomadaireController extends AbstractController
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
        $count = $capteurRepo->count(['idproject' => $idproject, 'idUser' => $userId]);
        if ($count === 0) {
            throw $this->createAccessDeniedException("Accès refusé : ce projet ne vous appartient pas.");
        }
    }

    // ========= AGRI HOME =========
    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releves-hebdo', name: 'agriculteur_releve_hebdo_home')]
    public function home(CapteurRepository $capteurRepo): Response
    {
        $userId = $this->getUserIdOrDeny();
        $projects = $capteurRepo->findDistinctProjectsByUser($userId);

        if (!$projects) {
            $this->addFlash('danger', "Aucun projet n'est lié à vos capteurs.");
            return $this->render('agriculteur/releve_hebdomadaire/choose_project.html.twig', [
                'projects' => [],
            ]);
        }

        if (count($projects) === 1) {
            return $this->redirectToRoute('agriculteur_releve_hebdo', ['idproject' => $projects[0]]);
        }

        return $this->render('agriculteur/releve_hebdomadaire/choose_project.html.twig', [
            'projects' => $projects,
        ]);
    }

    // ========= AGRI LISTE =========
    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releves-hebdo/{idproject}', name: 'agriculteur_releve_hebdo')]
    public function index(
        int $idproject,
        Request $request,
        CapteurRepository $capteurRepo,
        ReleveHebdomadaireRepository $repo
    ): Response {
        $this->denyIfProjectNotOwned($idproject, $capteurRepo);

        $items = $repo->findByProject($idproject, 20);

        $generateForm = $this->createForm(ReleveHebdomadaireGenerateType::class, [
            'weekStart' => new \DateTime('monday this week'),
        ], [
            'method' => 'POST',
            'action' => $this->generateUrl('agriculteur_releve_hebdo_generate', ['idproject' => $idproject]),
        ]);

        return $this->render('agriculteur/releve_hebdomadaire/index.html.twig', [
            'idproject' => $idproject,
            'items' => $items,
            'generateForm' => $generateForm->createView(),
        ]);
    }

    // ========= AGRI GENERATE =========
    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releves-hebdo/{idproject}/generate', name: 'agriculteur_releve_hebdo_generate', methods: ['POST'])]
    public function generate(
        int $idproject,
        Request $request,
        CapteurRepository $capteurRepo,
        ReleveHebdomadaireGenerator $generator
    ): Response {
        $this->denyIfProjectNotOwned($idproject, $capteurRepo);

        $form = $this->createForm(ReleveHebdomadaireGenerateType::class);
        $form->handleRequest($request);

        $weekStart = new \DateTime('monday this week');
        if ($form->isSubmitted() && $form->isValid()) {
            $weekStart = $form->get('weekStart')->getData();
        }

        $generator->generateForProjectWeek($idproject, $weekStart);

        $this->addFlash('success', 'Relevé hebdomadaire généré.');
        return $this->redirectToRoute('agriculteur_releve_hebdo', ['idproject' => $idproject]);
    }

    // ========= ADMIN =========
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/releves-hebdo', name: 'admin_releve_hebdo')]
    public function adminIndex(Request $request, ReleveHebdomadaireRepository $repo): Response
    {
        $idproject = $request->query->get('projet');
        $idproject = ($idproject !== null && $idproject !== '') ? (int)$idproject : null;

        $weekStart = $request->query->get('weekStart') ? new \DateTime($request->query->get('weekStart')) : null;

        $items = $repo->findAllByFilters($idproject, $weekStart, 500);

        $generateForm = $this->createForm(ReleveHebdomadaireGenerateType::class, [
            'idproject' => $idproject ?? 1,
            'weekStart' => new \DateTime('monday this week'),
        ], [
            'for_admin' => true,
            'method' => 'POST',
            'action' => $this->generateUrl('admin_releve_hebdo_generate'),
        ]);

        return $this->render('admin/releve_hebdomadaire/index.html.twig', [
            'items' => $items,
            'idproject' => $idproject,
            'weekStart' => $weekStart,
            'generateForm' => $generateForm->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/releves-hebdo/generate', name: 'admin_releve_hebdo_generate', methods: ['POST'])]
    public function adminGenerate(Request $request, ReleveHebdomadaireGenerator $generator): Response
    {
        $form = $this->createForm(ReleveHebdomadaireGenerateType::class, null, ['for_admin' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $idproject = (int)$form->get('idproject')->getData();
            $weekStart = $form->get('weekStart')->getData();

            $generator->generateForProjectWeek($idproject, $weekStart);
            $this->addFlash('success', "Relevé hebdomadaire généré pour projet #$idproject.");
        } else {
            $this->addFlash('danger', "Formulaire invalide.");
        }

        return $this->redirectToRoute('admin_releve_hebdo');
    }

    // ========= API EXPORT vers Module 5 =========
    // Module 5 (Python) peut appeler cette route pour récupérer les features
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/projets/{idproject}/releves-hebdo/latest', name: 'api_releve_hebdo_latest', methods: ['GET'])]
    public function apiLatest(int $idproject, ReleveHebdomadaireRepository $repo): Response
    {
        $latest = $repo->findLatestByProject($idproject);
        if (!$latest) {
            return $this->json(['message' => 'Aucun relevé hebdomadaire'], 404);
        }

        return $this->json([
            'idproject' => $latest->getIdproject(),
            'week_start' => $latest->getDateDebut()?->format('Y-m-d'),
            'week_end' => $latest->getDateFin()?->format('Y-m-d'),
            'temp_moyenne' => $latest->getTempMoyenne(),
            'humidite_moyenne' => $latest->getHumiditeMoyenne(),
            'condition_dominante' => $latest->getConditionDominante(),
            'nb_mesures_total' => $latest->getNbMesuresTotal(),
            'generated_at' => $latest->getDateGeneration()?->format('Y-m-d H:i:s'),
        ]);
    }
}