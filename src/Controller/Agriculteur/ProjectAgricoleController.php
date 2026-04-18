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

    #[Route('/newspaper', name: 'newspaper', methods: ['GET'])]
    public function newspaper(): Response
    {
        return $this->render('agriculteur/project_agricole/newspaper.html.twig');
    }

    #[Route('/newspaper/articles', name: 'newspaper_articles', methods: ['GET'])]
    public function newspaperArticles(Request $request, HttpClientInterface $httpClient): JsonResponse
    {
        $apiKey = trim((string) ($_ENV['GNEWS_API_KEY'] ?? $_SERVER['GNEWS_API_KEY'] ?? ''));
        if ($apiKey === '') {
            return $this->json([
                'ok' => false,
                'error' => 'La cle GNEWS_API_KEY est manquante.',
            ], 500);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = (int) $request->query->get('per_page', 12);
        $perPage = min(20, max(5, $perPage));
        $language = trim((string) $request->query->get('language', 'fr'));
        $language = in_array($language, ['fr', 'en', 'ar'], true) ? $language : 'fr';
        $userQuery = trim((string) $request->query->get('q', ''));
        $q = $userQuery !== '' ? $userQuery : 'agriculture OR farming OR crops OR livestock';

        $query = [
            'q' => $q,
            'lang' => $language,
            'max' => $perPage,
            'page' => $page,
            'sortby' => 'publishedAt',
            'apikey' => $apiKey,
        ];

        try {
            $requestOptions = [
                'query' => $query,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'Agrifund-NewsClient/1.0',
                ],
                'timeout' => 25,
            ];

            try {
                $apiResponse = $httpClient->request('GET', 'https://gnews.io/api/v4/search', $requestOptions);
                $statusCode = $apiResponse->getStatusCode();
                $rawBody = $apiResponse->getContent(false);
            } catch (\Throwable $firstError) {
                $retryOptions = $requestOptions;
                $retryOptions['verify_peer'] = false;
                $retryOptions['verify_host'] = false;
                $apiResponse = $httpClient->request('GET', 'https://gnews.io/api/v4/search', $retryOptions);
                $statusCode = $apiResponse->getStatusCode();
                $rawBody = $apiResponse->getContent(false);
            }

            $responseData = json_decode($rawBody, true);
            if (!is_array($responseData)) {
                return $this->json([
                    'ok' => false,
                    'error' => 'Reponse GNews invalide (non JSON).',
                ], 502);
            }

            if ($statusCode >= 400) {
                return $this->json([
                    'ok' => false,
                    'error' => (string) ($responseData['errors'][0] ?? $responseData['message'] ?? 'Erreur GNews.'),
                    'status' => $statusCode,
                ], 502);
            }

            $rawArticles = $responseData['articles'] ?? [];
            if (!is_array($rawArticles)) {
                $rawArticles = [];
            }

            $articles = array_values(array_map(static function ($item): array {
                if (!is_array($item)) {
                    return [];
                }

                $source = '';
                if (isset($item['source']) && is_array($item['source'])) {
                    $source = (string) ($item['source']['name'] ?? $item['source']['url'] ?? '');
                }

                return [
                    'id' => sha1((string) ($item['url'] ?? $item['title'] ?? uniqid('news_', true))),
                    'title' => (string) ($item['title'] ?? 'Sans titre'),
                    'description' => (string) ($item['description'] ?? ''),
                    'url' => (string) ($item['url'] ?? ''),
                    'image' => (string) ($item['image'] ?? ''),
                    'published_at' => (string) ($item['publishedAt'] ?? ''),
                    'source' => $source,
                    'author' => '',
                ];
            }, $rawArticles));

            $totalArticles = (int) ($responseData['totalArticles'] ?? 0);

            return $this->json([
                'ok' => true,
                'page' => $page,
                'has_next_pages' => ($page * $perPage) < $totalArticles,
                'articles' => array_values(array_filter($articles, static fn(array $a): bool => !empty($a))),
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'ok' => false,
                'error' => 'Impossible de recuperer les actualites GNews pour le moment.',
                'details' => (bool) $this->getParameter('kernel.debug') ? $e->getMessage() : null,
            ], 502);
        }
    }

    #[Route('/{id}/conseils-projet', name: 'project_advice', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function projectAdvice(int $id, ProjectAgricoleRepository $repo, HttpClientInterface $httpClient): Response
    {
        $project = $this->findOwnProject($id, $repo);
        if (!$project) {
            return $this->redirectToRoute('agriculteur_project_agricole_index');
        }

        $lat = $project->getLatitude();
        $lon = $project->getLongitude();
        if ($lat === null || $lon === null) {
            return $this->render('agriculteur/project_agricole/Conseils du projet.html.twig', [
                'project' => $project,
                'error' => 'Ce projet ne contient pas de coordonnees GPS. Ajoutez latitude/longitude dans le projet pour obtenir des conseils meteo.',
                'agro' => null,
                'advice' => [],
            ]);
        }

        $apiKey = trim((string) ($_ENV['OPENWEATHER_API_KEY'] ?? $_SERVER['OPENWEATHER_API_KEY'] ?? ''));
        if ($apiKey === '') {
            return $this->render('agriculteur/project_agricole/Conseils du projet.html.twig', [
                'project' => $project,
                'error' => 'La cle OPENWEATHER_API_KEY est manquante dans .env.local.',
                'agro' => null,
                'advice' => [],
            ]);
        }

        $weatherUrl = 'https://api.openweathermap.org/data/2.5/weather';
        $weatherQuery = [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $apiKey,
            'units' => 'metric',
            'lang' => 'fr',
        ];

        try {
            try {
                $weatherResp = $httpClient->request('GET', $weatherUrl, [
                    'query' => $weatherQuery,
                    'timeout' => 20,
                ]);
                $weather = $weatherResp->toArray(false);
            } catch (\Throwable $sslError) {
                $weatherResp = $httpClient->request('GET', $weatherUrl, [
                    'query' => $weatherQuery,
                    'timeout' => 20,
                    'verify_peer' => false,
                    'verify_host' => false,
                ]);
                $weather = $weatherResp->toArray(false);
            }

            $uvi = null;
            try {
                $uviResp = $httpClient->request('GET', 'https://api.openweathermap.org/data/3.0/onecall', [
                    'query' => [
                        'lat' => $lat,
                        'lon' => $lon,
                        'exclude' => 'minutely,hourly,daily,alerts',
                        'appid' => $apiKey,
                        'units' => 'metric',
                        'lang' => 'fr',
                    ],
                    'timeout' => 20,
                ]);
                $uviData = $uviResp->toArray(false);
                $uvi = isset($uviData['current']['uvi']) ? (float) $uviData['current']['uvi'] : null;
            } catch (\Throwable $ignored) {
                $uvi = null;
            }

            $agro = [
                'temperature' => isset($weather['main']['temp']) ? (float) $weather['main']['temp'] : null,
                'humidity' => isset($weather['main']['humidity']) ? (float) $weather['main']['humidity'] : null,
                'wind' => isset($weather['wind']['speed']) ? (float) $weather['wind']['speed'] : null,
                'pressure' => isset($weather['main']['pressure']) ? (float) $weather['main']['pressure'] : null,
                'clouds' => isset($weather['clouds']['all']) ? (float) $weather['clouds']['all'] : null,
                'conditions' => (string) ($weather['weather'][0]['description'] ?? 'N/A'),
                'uvi' => $uvi,
                'location_name' => (string) ($weather['name'] ?? 'Localisation du projet'),
                'lat' => $lat,
                'lon' => $lon,
            ];

            $advice = $this->buildAgroAdvice($agro);

            return $this->render('agriculteur/project_agricole/Conseils du projet.html.twig', [
                'project' => $project,
                'agro' => $agro,
                'advice' => $advice,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            return $this->render('agriculteur/project_agricole/Conseils du projet.html.twig', [
                'project' => $project,
                'error' => 'Impossible de recuperer les donnees meteo OpenWeather pour ce projet.',
                'agro' => null,
                'advice' => [],
            ]);
        }
    }

    private function buildAgroAdvice(array $agro): array
    {
        $tips = [];

        $temp = $agro['temperature'] ?? null;
        if (is_numeric($temp)) {
            $t = number_format((float) $temp, 1, '.', '');
            if ($temp <= 2) {
                $tips[] = '❄ Temperature tres froide (' . $t . ' °C) — Risque de gel. Protegez les cultures fragiles et evitez les semis.';
            } elseif ($temp < 12) {
                $tips[] = '🌤 Temperature fraiche (' . $t . ' °C) — Conditions favorables pour ble, orge et legumes d hiver.';
            } elseif ($temp <= 28) {
                $tips[] = '☀ Temperature optimale (' . $t . ' °C) — Ideal pour la croissance active des cultures.';
            } elseif ($temp < 35) {
                $tips[] = '🌡 Chaleur elevee (' . $t . ' °C) — Augmentez l irrigation. Preferez les travaux tot le matin.';
            } else {
                $tips[] = '🔥 Chaleur extreme (' . $t . ' °C) — Stress hydrique eleve. Irrigation renforcee et paillage recommande.';
            }
        }

        $humidity = $agro['humidity'] ?? null;
        if (is_numeric($humidity)) {
            $h = number_format((float) $humidity, 0, '.', '');
            if ($humidity < 35) {
                $tips[] = '💧 Humidite tres basse (' . $h . ' %) — Irrigation urgente recommandee. Risque de stress hydrique.';
            } elseif ($humidity < 55) {
                $tips[] = '💧 Humidite moderee (' . $h . ' %) — Surveillez le sol. Irrigation legere possible.';
            } elseif ($humidity <= 80) {
                $tips[] = '💧 Humidite suffisante (' . $h . ' %) — Bon niveau hydrique. Vigilance fongique si cette situation dure.';
            } else {
                $tips[] = '💧 Humidite tres elevee (' . $h . ' %) — Risque fongique. Assurez un bon drainage et l aeration.';
            }
        }

        // Estimation simple de l etat hydrique du sol a partir des donnees dispo.
        $clouds = $agro['clouds'] ?? null;
        $conditions = mb_strtolower((string) ($agro['conditions'] ?? ''));
        $rainLike = str_contains($conditions, 'rain')
            || str_contains($conditions, 'pluie')
            || str_contains($conditions, 'drizzle')
            || str_contains($conditions, 'averse');
        if (is_numeric($humidity) && is_numeric($clouds)) {
            if ($humidity < 40 && $clouds < 35 && !$rainLike) {
                $tips[] = '🌍 Sol sec (estime) — Irrigation immediate necessaire.';
            } elseif (($humidity >= 40 && $humidity <= 75) && !$rainLike) {
                $tips[] = '🌍 Humidite du sol correcte (estimee) — Conditions favorables pour les racines.';
            } else {
                $tips[] = '🌍 Sol tres humide (estime) — Reduisez l irrigation et verifiez le drainage.';
            }
        }

        $wind = $agro['wind'] ?? null;
        if (is_numeric($wind)) {
            $w = number_format((float) $wind, 1, '.', '');
            if ($wind >= 10) {
                $tips[] = '💨 Vent fort (' . $w . ' m/s) — Evitez les traitements phytosanitaires. Proteger les jeunes plants.';
            } elseif ($wind >= 5) {
                $tips[] = '💨 Vent modere (' . $w . ' m/s) — Conditions moyennes pour les epandages.';
            } else {
                $tips[] = '💨 Vent faible (' . $w . ' m/s) — Bonnes conditions pour les traitements localises.';
            }
        }

        $pressure = $agro['pressure'] ?? null;
        if (is_numeric($pressure)) {
            $p = number_format((float) $pressure, 0, '.', '');
            if ($pressure < 1005) {
                $tips[] = '🌫 Pression basse (' . $p . ' hPa) — Meteo instable/pluie possible. Anticipez les travaux sensibles.';
            } elseif ($pressure > 1020) {
                $tips[] = '🧭 Pression elevee (' . $p . ' hPa) — Conditions plutot stables et seches. Surveillez l evaporation.';
            } else {
                $tips[] = '🌫 Pression moderee (' . $p . ' hPa) — Conditions meteo relativement normales.';
            }
        }

        if (is_numeric($clouds)) {
            $c = number_format((float) $clouds, 0, '.', '');
            if ($clouds < 20) {
                $tips[] = '🌾 Ciel degage (' . $c . ' %) — Forte lumiere, photosynthese active et evaporation plus rapide.';
            } elseif ($clouds <= 70) {
                $tips[] = '⛅ Ciel partiellement nuageux (' . $c . ' %) — Conditions globalement equilibrees pour la plupart des cultures.';
            } else {
                $tips[] = '☁ Nebulosite elevee (' . $c . ' %) — Rayonnement reduit. Ajustez irrigation/fertilisation.';
            }
        }

        $uvi = $agro['uvi'] ?? null;
        if (is_numeric($uvi)) {
            $u = number_format((float) $uvi, 1, '.', '');
            if ($uvi >= 8) {
                $tips[] = '☀ UV eleve (' . $u . ') — Travaillez tot le matin ou en soiree. Protection individuelle obligatoire.';
            } elseif ($uvi >= 6) {
                $tips[] = '🔆 UV modere/eleve (' . $u . ') — Surveillez les jeunes plants et limitez les interventions en plein midi.';
            } else {
                $tips[] = '🕶 UV faible a modere (' . $u . ') — Risque solaire limite pour les cultures et operateurs.';
            }
        }

        if ($conditions !== '') {
            if (str_contains($conditions, 'rain') || str_contains($conditions, 'pluie')) {
                $tips[] = '🌧 Conditions pluvieuses — Reportez les traitements foliaires et surveillez le drainage.';
            }
            if (str_contains($conditions, 'thunderstorm') || str_contains($conditions, 'orage')) {
                $tips[] = '⛈ Risque orageux — Protegez le materiel et evitez les interventions en parcelle.';
            }
            if (str_contains($conditions, 'fog') || str_contains($conditions, 'brouillard')) {
                $tips[] = '🌫 Brouillard — Vigilance accrue sur les maladies cryptogamiques.';
            }
        }

        if (empty($tips)) {
            $tips[] = '✅ Conditions globalement favorables. Maintenez le suivi hydrique et sanitaire habituel.';
        }

        return $tips;
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
