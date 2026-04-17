<?php

namespace App\Controller\Agriculteur;

use App\Entity\ProjectAgricole;
use App\Form\ProjectAgricoleAgriculteurType;
use App\Repository\ProjectAgricoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
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

    #[Route('/{id}/plant-disease-assistant', name: 'plant_disease_assistant', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function plantDiseaseAssistant(int $id, ProjectAgricoleRepository $repo): Response
    {
        $project = $this->findOwnProject($id, $repo);

        if (!$project) {
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        return $this->render('agriculteur/project_agricole/Plant Disease Assistant.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/plant-disease-assistant/analyze', name: 'plant_disease_assistant_analyze', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function analyzePlantDisease(
        int $id,
        Request $request,
        ProjectAgricoleRepository $repo,
        HttpClientInterface $httpClient
    ): JsonResponse {
        $project = $this->findOwnProject($id, $repo);

        if (!$project) {
            return $this->json([
                'ok' => false,
                'error' => 'Projet introuvable.',
            ], 404);
        }

        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('plant_disease_' . $project->getIdproject(), $csrfToken)) {
            return $this->json([
                'ok' => false,
                'error' => 'Jeton de securite invalide.',
            ], 403);
        }

        $image = $request->files->get('plant_image');
        if (!$image) {
            return $this->json([
                'ok' => false,
                'error' => 'Veuillez importer une image de plante.',
            ], 400);
        }

        $mimeType = (string) $image->getMimeType();
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            return $this->json([
                'ok' => false,
                'error' => 'Format non supporte. Utilisez JPG, PNG ou WebP.',
            ], 400);
        }

        $maxSizeBytes = 6 * 1024 * 1024;
        if ((int) $image->getSize() > $maxSizeBytes) {
            return $this->json([
                'ok' => false,
                'error' => 'Image trop lourde. Maximum autorise: 6 MB.',
            ], 400);
        }

        $apiKey = trim((string) ($_ENV['GROQ_API_KEY'] ?? $_SERVER['GROQ_API_KEY'] ?? ''));
        if ($apiKey === '') {
            return $this->json([
                'ok' => false,
                'error' => 'La cle API GROQ_API_KEY est manquante dans l environnement.',
            ], 500);
        }

        $projectName = (string) ($project->getNomproject() ?? 'Projet agricole');
        $prompt = "Tu es un expert agronome. "
            . "Le projet agricole s'appelle : \"" . $projectName . "\". "
            . "Identifie immediatement le nom de la plante sur cette image, "
            . "analyse la maladie visible et donne un traitement agricole concret. "
            . "Reponds en francais, structure ainsi :\n"
            . "🌱 Plante : [nom]\n"
            . "🦠 Maladie detectee : [nom de la maladie]\n"
            . "⚠ Symptomes observes : [description courte]\n"
            . "💊 Traitement recommande :\n  • [produit/methode 1]\n  • [produit/methode 2]\n  • [produit/methode 3]\n"
            . "🔄 Prevention : [conseil court]\n"
            . "Si la plante est saine, indique-le clairement.";

        $model = trim((string) ($_ENV['GROQ_VISION_MODEL'] ?? $_SERVER['GROQ_VISION_MODEL'] ?? 'meta-llama/llama-4-scout-17b-16e-instruct'));
        $imageContent = @file_get_contents($image->getPathname());
        if ($imageContent === false) {
            return $this->json([
                'ok' => false,
                'error' => 'Impossible de lire le fichier image.',
            ], 400);
        }

        $payload = [
            'model' => $model,
            'temperature' => 0.2,
            'max_tokens' => 700,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:' . $mimeType . ';base64,' . base64_encode($imageContent),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        try {
            $apiResponse = $httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 60,
            ]);

            $statusCode = $apiResponse->getStatusCode();
            $responseData = $apiResponse->toArray(false);

            if ($statusCode >= 400) {
                $error = (string) ($responseData['error']['message'] ?? 'Erreur renvoyee par le fournisseur IA.');
                return $this->json([
                    'ok' => false,
                    'error' => $error,
                ], 502);
            }

            $analysis = $responseData['choices'][0]['message']['content'] ?? null;
            if (!is_string($analysis) || trim($analysis) === '') {
                return $this->json([
                    'ok' => false,
                    'error' => 'Aucune analyse exploitable n a ete retournee.',
                ], 502);
            }

            return $this->json([
                'ok' => true,
                'analysis' => trim($analysis),
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'ok' => false,
                'error' => 'Impossible de contacter le service IA pour le moment.',
            ], 502);
        }
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
