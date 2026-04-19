<?php

namespace App\Controller;

use App\Repository\CapteurRepository;
use App\Service\Weather\OpenWeatherClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/meteo')]
class MeteoController extends AbstractController
{
    #[Route('/projet/{idproject}', name: 'meteo_projet')]
    public function meteoProjet(
        int $idproject,
        Request $request,
        CapteurRepository $capteurRepo,
        OpenWeatherClient $weather
    ): Response {
        $user = $this->getUser();

        // sécurité : projet appartient à l'agriculteur via capteurs
        $count = $capteurRepo->count([
            'idproject' => $idproject,
            'idUser' => $user->getId()
        ]);
        if ($count === 0 && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Projet non autorisé.");
        }

        // 1) ville via query param
        $ville = trim((string) $request->query->get('ville', ''));

        // 2) fallback : localisation du premier capteur
        if ($ville === '') {
            $capteur = $capteurRepo->findOneBy([
                'idproject' => $idproject,
                'idUser' => $user->getId()
            ]);
            if ($capteur && $capteur->getLocalisation()) {
                // IMPORTANT: localisation doit être une ville (ex: "Rabat")
                $ville = trim($capteur->getLocalisation());
            }
        }

        // 3) si toujours pas de ville => page formulaire
        if ($ville === '') {
            return $this->render('meteo/choose_city.html.twig', [
                'idproject' => $idproject
            ]);
        }

        try {
            $current = $weather->getCurrentByCity($ville);
            $forecast = $weather->getForecastByCity($ville);
        } catch (\Throwable $e) {
            // ville invalide ou erreur API
            return $this->render('meteo/choose_city.html.twig', [
                'idproject' => $idproject,
                'error' => "Ville introuvable ou erreur météo. Essaie: Rabat, Casablanca, Marrakech..."
            ]);
        }

        return $this->render('meteo/projet_city.html.twig', [
            'idproject' => $idproject,
            'ville' => $ville,
            'current' => $current,
            'forecast' => $forecast
        ]);
    }
}