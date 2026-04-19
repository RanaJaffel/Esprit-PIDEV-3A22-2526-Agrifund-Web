<?php

namespace App\Controller;

use App\Entity\ReleveTerrain;
use App\Form\ReleveTerrainAnnotationType;
use App\Form\ReleveTerrainType;
use App\Repository\CapteurRepository;
use App\Repository\ReleveTerrainRepository;
use App\Service\SensorAiAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReleveTerrainController extends AbstractController
{
    public function __construct(
        private SensorAiAnalysisService $sensorAiAnalysisService
    ) {
    }

    private function isProjectOwned(int $idproject, CapteurRepository $capteurRepo): bool
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            return false;
        }

        return $capteurRepo->count([
            'idproject' => $idproject,
            'idUser' => (int) $user->getId(),
        ]) > 0;
    }

    private function isCapteurAllowed(int $idCapteur, int $idproject, CapteurRepository $capteurRepo): bool
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            return false;
        }

        $capteur = $capteurRepo->findOneBy([
            'idCapteur' => $idCapteur,
            'idproject' => $idproject,
            'idUser' => (int) $user->getId(),
        ]);

        return $capteur !== null;
    }

    private function buildCapteurChoices(CapteurRepository $capteurRepo, int $idproject): array
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            return [];
        }

        $capteurs = $capteurRepo->findBy([
            'idproject' => $idproject,
            'idUser' => (int) $user->getId(),
        ], ['idCapteur' => 'DESC']);

        $choices = [];
        foreach ($capteurs as $capteur) {
            $label = sprintf(
                'Capteur #%d - %s (%s)',
                $capteur->getIdCapteur(),
                $capteur->getTypeCapteur(),
                $capteur->getLocalisation()
            );
            $choices[$label] = $capteur->getIdCapteur();
        }

        return $choices;
    }

    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releve-terrain/{idproject}', name: 'agriculteur_releve_terrain')]
    public function agriculteurIndex(
        int $idproject,
        Request $request,
        ReleveTerrainRepository $releveRepo,
        CapteurRepository $capteurRepo
    ): Response {
        if (!$this->isProjectOwned($idproject, $capteurRepo)) {
            $this->addFlash('error', 'Acces refuse : ce projet ne vous appartient pas.');
            return $this->redirectToRoute('agriculteur_dashboard');
        }

        $typeMesure = (string) $request->query->get('type', '');

        $mesures = $typeMesure !== ''
            ? $releveRepo->findByProjectAndType($idproject, $typeMesure)
            : $releveRepo->findByProject($idproject);

        return $this->render('agriculteur/releve_terrain/index.html.twig', [
            'mesures' => $mesures,
            'idproject' => $idproject,
            'typeMesure' => $typeMesure,
            'types' => $releveRepo->findTypesByProject($idproject),
        ]);
    }

    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releve-terrain/{idproject}/new', name: 'agriculteur_releve_terrain_new')]
    public function new(
        int $idproject,
        Request $request,
        EntityManagerInterface $em,
        CapteurRepository $capteurRepo
    ): Response {
        if (!$this->isProjectOwned($idproject, $capteurRepo)) {
            $this->addFlash('error', 'Acces refuse : ce projet ne vous appartient pas.');
            return $this->redirectToRoute('agriculteur_dashboard');
        }

        $releve = new ReleveTerrain();
        $releve->setIdproject($idproject);
        $releve->setSourceDonnee('MANUEL');

        $capteurChoices = $this->buildCapteurChoices($capteurRepo, $idproject);
        if ($capteurChoices === []) {
            $this->addFlash('warning', "Aucun capteur disponible pour ce projet. Ajoutez d'abord un capteur.");
            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $idproject,
            ]);
        }

        $form = $this->createForm(ReleveTerrainType::class, $releve, [
            'capteur_choices' => $capteurChoices,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isCapteurAllowed((int) $releve->getIdCapteur(), $idproject, $capteurRepo)) {
                $this->addFlash('error', 'Capteur non autorise pour ce projet.');
                return $this->redirectToRoute('agriculteur_releve_terrain', [
                    'idproject' => $idproject,
                ]);
            }

            $releve->setIdproject($idproject);
            $releve->setSourceDonnee('MANUEL');

            $em->persist($releve);
            $em->flush();

            $this->sensorAiAnalysisService->analyze(
                $releve->getIdproject(),
                $releve->getIdCapteur(),
                $releve->getTypeMesure(),
                $releve->getValeurMesuree()
            );

            $this->addFlash('success', 'Mesure manuelle ajoutee.');

            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $idproject,
            ]);
        }

        return $this->render('agriculteur/releve_terrain/new.html.twig', [
            'form' => $form->createView(),
            'idproject' => $idproject,
        ]);
    }

    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releve-terrain/edit/{id}', name: 'agriculteur_releve_terrain_edit')]
    public function edit(
        int $id,
        Request $request,
        ReleveTerrainRepository $releveRepo,
        CapteurRepository $capteurRepo,
        EntityManagerInterface $em
    ): Response {
        $releve = $releveRepo->find($id);
        if (!$releve) {
            throw $this->createNotFoundException('Mesure introuvable');
        }

        if (!$this->isProjectOwned($releve->getIdproject(), $capteurRepo)) {
            $this->addFlash('error', 'Acces refuse : ce projet ne vous appartient pas.');
            return $this->redirectToRoute('agriculteur_dashboard');
        }

        if ($releve->getSourceDonnee() !== 'MANUEL') {
            $this->addFlash('warning', "Mesure simulateur non modifiable. Utilise 'Annoter'.");
            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $releve->getIdproject(),
            ]);
        }

        $form = $this->createForm(ReleveTerrainType::class, $releve, [
            'capteur_choices' => $this->buildCapteurChoices($capteurRepo, $releve->getIdproject()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isCapteurAllowed((int) $releve->getIdCapteur(), $releve->getIdproject(), $capteurRepo)) {
                $this->addFlash('error', 'Capteur non autorise pour ce projet.');
                return $this->redirectToRoute('agriculteur_releve_terrain', [
                    'idproject' => $releve->getIdproject(),
                ]);
            }

            $releve->setSourceDonnee('MANUEL');
            $em->flush();

            $this->addFlash('success', 'Mesure manuelle modifiee.');

            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $releve->getIdproject(),
            ]);
        }

        return $this->render('agriculteur/releve_terrain/edit.html.twig', [
            'form' => $form->createView(),
            'releve' => $releve,
        ]);
    }

    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releve-terrain/delete/{id}', name: 'agriculteur_releve_terrain_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        ReleveTerrainRepository $releveRepo,
        CapteurRepository $capteurRepo,
        EntityManagerInterface $em
    ): Response {
        $releve = $releveRepo->find($id);
        if (!$releve) {
            throw $this->createNotFoundException('Mesure introuvable');
        }

        if (!$this->isProjectOwned($releve->getIdproject(), $capteurRepo)) {
            $this->addFlash('error', 'Acces refuse : ce projet ne vous appartient pas.');
            return $this->redirectToRoute('agriculteur_dashboard');
        }

        if ($releve->getSourceDonnee() !== 'MANUEL') {
            $this->addFlash('warning', 'Mesure simulateur non supprimable.');
            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $releve->getIdproject(),
            ]);
        }

        if ($this->isCsrfTokenValid('delete' . $id, (string) $request->request->get('_token'))) {
            $idproject = $releve->getIdproject();
            $em->remove($releve);
            $em->flush();

            $this->addFlash('success', 'Mesure manuelle supprimee.');

            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $idproject,
            ]);
        }

        $this->addFlash('error', 'Token CSRF invalide.');

        return $this->redirectToRoute('agriculteur_releve_terrain', [
            'idproject' => $releve->getIdproject(),
        ]);
    }

    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releve-terrain/annoter/{id}', name: 'agriculteur_releve_terrain_annoter')]
    public function annoter(
        int $id,
        Request $request,
        ReleveTerrainRepository $releveRepo,
        CapteurRepository $capteurRepo,
        EntityManagerInterface $em
    ): Response {
        $releve = $releveRepo->find($id);
        if (!$releve) {
            throw $this->createNotFoundException('Mesure introuvable');
        }

        if (!$this->isProjectOwned($releve->getIdproject(), $capteurRepo)) {
            $this->addFlash('error', 'Acces refuse : ce projet ne vous appartient pas.');
            return $this->redirectToRoute('agriculteur_dashboard');
        }

        $form = $this->createForm(ReleveTerrainAnnotationType::class, $releve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Annotation enregistree.');

            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $releve->getIdproject(),
            ]);
        }

        return $this->render('agriculteur/releve_terrain/annoter.html.twig', [
            'form' => $form->createView(),
            'releve' => $releve,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/releve-terrain', name: 'admin_releve_terrain')]
    public function adminIndex(
        Request $request,
        ReleveTerrainRepository $releveRepo
    ): Response {
        $idproject = (string) $request->query->get('projet', '');
        $typeMesure = (string) $request->query->get('type', '');

        if ($idproject !== '' && $typeMesure !== '') {
            $mesures = $releveRepo->findByProjectAndType((int) $idproject, $typeMesure);
        } elseif ($idproject !== '') {
            $mesures = $releveRepo->findByProject((int) $idproject);
        } else {
            $mesures = $releveRepo->findAllRecent(500);
        }

        return $this->render('admin/releve_terrain/index.html.twig', [
            'mesures' => $mesures,
            'idproject' => $idproject,
            'typeMesure' => $typeMesure,
            'types' => $releveRepo->findAllTypes(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/releve-terrain/delete/{id}', name: 'admin_releve_terrain_delete', methods: ['POST'])]
    public function adminDelete(
        int $id,
        Request $request,
        ReleveTerrainRepository $releveRepo,
        EntityManagerInterface $em
    ): Response {
        $releve = $releveRepo->find($id);

        if ($releve && $this->isCsrfTokenValid('delete' . $id, (string) $request->request->get('_token'))) {
            $em->remove($releve);
            $em->flush();
            $this->addFlash('success', 'Mesure supprimee par l admin.');
        }

        return $this->redirectToRoute('admin_releve_terrain');
    }

    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releve-terrain', name: 'agriculteur_releve_terrain_home')]
    public function releveHome(CapteurRepository $capteurRepo): Response
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException();
        }

        $projects = $capteurRepo->findDistinctProjectsByUser((int) $user->getId());

        if ($projects === []) {
            $this->addFlash('danger', "Aucun projet n'est lie a vos capteurs. Ajoutez ou affectez un capteur a un projet.");
            return $this->redirectToRoute('capteur_index');
        }

        if (count($projects) === 1) {
            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $projects[0],
            ]);
        }

        return $this->render('agriculteur/releve_terrain/choose_project.html.twig', [
            'projects' => $projects,
        ]);
    }
}
