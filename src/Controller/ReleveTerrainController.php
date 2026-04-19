<?php

namespace App\Controller;

use App\Entity\ReleveTerrain;
use App\Form\ReleveTerrainType;
use App\Form\ReleveTerrainAnnotationType;
use App\Repository\CapteurRepository;
use App\Repository\ReleveTerrainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\SensorAiAnalysisService;

class ReleveTerrainController extends AbstractController
{
    // ===========================
    // Helpers sécurité
    // ===========================

    private function getUserIdOrDeny(): int
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            $this->addFlash('error', 'Vous devez être connecté.');
            // On lève quand même car on ne peut pas rediriger ici
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }
        return (int) $user->getId();
    }

    /**
     * Retourne true si le projet appartient à l'utilisateur
     * (au lieu de lancer une exception directement)
     */
    private function isProjectOwned(int $idproject, CapteurRepository $capteurRepo): bool
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            return false;
        }

        $userId = (int) $user->getId();

        $count = $capteurRepo->count([
            'idproject' => $idproject,
            'idUser'    => $userId,
        ]);

        return $count > 0;
    }

    /**
     * Vérifie qu'un capteur appartient au user + projet
     * Retourne true si autorisé
     */
    private function isCapteurAllowed(int $idCapteur, int $idproject, CapteurRepository $capteurRepo): bool
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            return false;
        }

        $userId = (int) $user->getId();

        $capteur = $capteurRepo->findOneBy([
            'idCapteur' => $idCapteur,
            'idproject' => $idproject,
            'idUser'    => $userId,
        ]);

        return $capteur !== null;
    }

    /**
     * Pour alimenter ChoiceType(idCapteur) dans ReleveTerrainType
     */
    private function buildCapteurChoices(CapteurRepository $capteurRepo, int $idproject): array
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            return [];
        }

        $userId = (int) $user->getId();

        $capteurs = $capteurRepo->findBy([
    'idproject' => $idproject,
], ['idCapteur' => 'DESC']);

        $choices = [];
        foreach ($capteurs as $c) {
            $label = sprintf(
                'Capteur #%d - %s (%s)',
                $c->getIdCapteur(),
                $c->getTypeCapteur(),
                $c->getLocalisation()
            );
            $choices[$label] = $c->getIdCapteur();
        }
        return $choices;
    }

    // =========================================================
    // AGRICULTEUR — LISTE + FILTRE
    // =========================================================
    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releve-terrain/{idproject}', name: 'agriculteur_releve_terrain')]
    public function agriculteurIndex(
        int $idproject,
        Request $request,
        ReleveTerrainRepository $releveRepo,
        CapteurRepository $capteurRepo
    ): Response {
        // ✅ Flash + redirect au lieu de l'exception
        if (!$this->isProjectOwned($idproject, $capteurRepo)) {
            $this->addFlash('error', 'Accès refusé : ce projet ne vous appartient pas.');
            return $this->redirectToRoute('agriculteur_dashboard'); // ← adapte la route
        }

        $typeMesure = (string) $request->query->get('type', '');

        $mesures = $typeMesure !== ''
            ? $releveRepo->findByProjectAndType($idproject, $typeMesure)
            : $releveRepo->findByProject($idproject);

        $types = $releveRepo->findTypesByProject($idproject);

        return $this->render('agriculteur/releve_terrain/index.html.twig', [
            'mesures'    => $mesures,
            'idproject'  => $idproject,
            'typeMesure' => $typeMesure,
            'types'      => $types,
        ]);
    }

    // =========================================================
    // AGRICULTEUR — CREATE (MANUEL)
    // =========================================================
    #[IsGranted('ROLE_AGRICULTEUR')]
    #[Route('/agriculteur/releve-terrain/{idproject}/new', name: 'agriculteur_releve_terrain_new')]
    public function new(
        int $idproject,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $releve = new ReleveTerrain();
        $form = $this->createForm(ReleveTerrainType::class, $releve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($releve);
            $em->flush();

            // ✅ IA appelée ici
            $this->sensorAiAnalysisService->analyze(
                $releve->getIdproject(),
                $releve->getIdCapteur(),
                $releve->getTypeMesure(),
                $releve->getValeurMesuree()
            );

            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $idproject
            ]);
        }

        return $this->render('agriculteur/releve_terrain/new.html.twig', [
    'form' => $form->createView(),
    'idproject' => $idproject   // ✅ AJOUTER CETTE LIGNE
]);
    }


    // =========================================================
    // AGRICULTEUR — EDIT (MANUEL UNIQUEMENT)
    // =========================================================
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

        // ✅ Flash + redirect
        if (!$this->isProjectOwned($releve->getIdproject(), $capteurRepo)) {
            $this->addFlash('error', 'Accès refusé : ce projet ne vous appartient pas.');
            return $this->redirectToRoute('agriculteur_dashboard');
        }

        if ($releve->getSourceDonnee() !== 'MANUEL') {
            $this->addFlash('warning', "❌ Mesure SIMULATEUR non modifiable. Utilise 'Annoter'.");
            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $releve->getIdproject(),
            ]);
        }

        $capteurChoices = $this->buildCapteurChoices($capteurRepo, $releve->getIdproject());

        $form = $this->createForm(ReleveTerrainType::class, $releve, [
            'capteur_choices' => $capteurChoices,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ✅ Flash + redirect
            if (!$this->isCapteurAllowed($releve->getIdCapteur(), $releve->getIdproject(), $capteurRepo)) {
                $this->addFlash('error', '❌ Capteur non autorisé pour ce projet.');
                return $this->redirectToRoute('agriculteur_releve_terrain', [
                    'idproject' => $releve->getIdproject(),
                ]);
            }

            $releve->setSourceDonnee('MANUEL');
            $em->flush();

            $this->addFlash('success', '✅ Mesure manuelle modifiée !');

            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $releve->getIdproject(),
            ]);
        }

        return $this->render('agriculteur/releve_terrain/edit.html.twig', [
            'form'   => $form->createView(),
            'releve' => $releve,
        ]);
    }

    // =========================================================
    // AGRICULTEUR — DELETE (MANUEL UNIQUEMENT)
    // =========================================================
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

        // ✅ Flash + redirect
        if (!$this->isProjectOwned($releve->getIdproject(), $capteurRepo)) {
            $this->addFlash('error', 'Accès refusé : ce projet ne vous appartient pas.');
            return $this->redirectToRoute('agriculteur_dashboard');
        }

        if ($releve->getSourceDonnee() !== 'MANUEL') {
            $this->addFlash('warning', '❌ Mesure SIMULATEUR non supprimable.');
            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $releve->getIdproject(),
            ]);
        }

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $idproject = $releve->getIdproject();
            $em->remove($releve);
            $em->flush();

            $this->addFlash('success', '🗑️ Mesure manuelle supprimée !');

            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $idproject,
            ]);
        }

        $this->addFlash('error', 'Token CSRF invalide.');

        return $this->redirectToRoute('agriculteur_releve_terrain', [
            'idproject' => $releve->getIdproject(),
        ]);
    }

    // =========================================================
    // AGRICULTEUR — ANNOTER
    // =========================================================
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

        // ✅ Flash + redirect
        if (!$this->isProjectOwned($releve->getIdproject(), $capteurRepo)) {
            $this->addFlash('error', 'Accès refusé : ce projet ne vous appartient pas.');
            return $this->redirectToRoute('agriculteur_dashboard');
        }

        $form = $this->createForm(ReleveTerrainAnnotationType::class, $releve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Annotation enregistrée !');

            return $this->redirectToRoute('agriculteur_releve_terrain', [
                'idproject' => $releve->getIdproject(),
            ]);
        }

        return $this->render('agriculteur/releve_terrain/annoter.html.twig', [
            'form'   => $form->createView(),
            'releve' => $releve,
        ]);
    }

    // =========================================================
    // ADMIN — LISTE + FILTRES
    // =========================================================
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/releve-terrain', name: 'admin_releve_terrain')]
    public function adminIndex(
        Request $request,
        ReleveTerrainRepository $releveRepo
    ): Response {
        $idproject  = $request->query->get('projet', '');
        $typeMesure = $request->query->get('type', '');

        if ($idproject !== '' && $typeMesure !== '') {
            $mesures = $releveRepo->findByProjectAndType((int)$idproject, (string)$typeMesure);
        } elseif ($idproject !== '') {
            $mesures = $releveRepo->findByProject((int)$idproject);
        } else {
            $mesures = $releveRepo->findAllRecent(500);
        }

        $types = $releveRepo->findAllTypes();

        return $this->render('admin/releve_terrain/index.html.twig', [
            'mesures'    => $mesures,
            'idproject'  => $idproject,
            'typeMesure' => $typeMesure,
            'types'      => $types,
        ]);
    }

    // =========================================================
    // ADMIN — DELETE
    // =========================================================
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/releve-terrain/delete/{id}', name: 'admin_releve_terrain_delete', methods: ['POST'])]
    public function adminDelete(
        int $id,
        Request $request,
        ReleveTerrainRepository $releveRepo,
        EntityManagerInterface $em
    ): Response {
        $releve = $releveRepo->find($id);

        if ($releve && $this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $em->remove($releve);
            $em->flush();
            $this->addFlash('success', '🗑️ Mesure supprimée (admin).');
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

    // aucun projet lié
    if (count($projects) === 0) {
        $this->addFlash('danger', "Aucun projet n'est lié à vos capteurs. Ajoutez/affectez un capteur à un projet.");
        return $this->redirectToRoute('capteur_index');
    }

    // 1 seul projet => redirection automatique (UX pro)
    if (count($projects) === 1) {
        return $this->redirectToRoute('agriculteur_releve_terrain', [
            'idproject' => $projects[0],
        ]);
    }

    // plusieurs projets => page de choix
    return $this->render('agriculteur/releve_terrain/choose_project.html.twig', [
        'projects' => $projects,
    ]);
}
}