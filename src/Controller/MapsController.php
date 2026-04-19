<?php

namespace App\Controller;

use App\Repository\CapteurRepository;
use App\Service\Maps\NominatimClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/maps')]
class MapsController extends AbstractController
{
    #[Route('/projet/{idproject}', name: 'maps_projet')]
    public function projet(
        int $idproject,
        Request $request,
        CapteurRepository $capteurRepo,
        NominatimClient $geo
    ): Response {
        $user = $this->getUser();

        // sécurité : projet appartient à user via capteurs (comme ton dashboard)
        $count = $capteurRepo->count([
            'idproject' => $idproject,
            'idUser' => $user->getId(),
        ]);

        if ($count === 0 && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Accès refusé : projet non autorisé.");
        }

        // query: ville/adresse
        $query = trim((string) $request->query->get('q', ''));

        // fallback: utiliser localisation du 1er capteur si vide
        if ($query === '') {
            $capteur = $capteurRepo->findOneBy([
                'idproject' => $idproject,
                'idUser' => $user->getId()
            ]);
            if ($capteur && $capteur->getLocalisation()) {
                $query = $capteur->getLocalisation(); // idéalement une ville
            }
        }

        $result = null;
        if ($query !== '') {
            $result = $geo->geocode($query);
        }

        return $this->render('maps/projet.html.twig', [
            'idproject' => $idproject,
            'query' => $query,
            'geo' => $result, // null ou lat/lon/display_name
        ]);
    }
}