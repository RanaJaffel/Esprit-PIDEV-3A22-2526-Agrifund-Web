<?php

namespace App\Controller\Banque;

use App\Entity\EvaluationRisque;
use App\Form\EvaluationRisqueType;
use App\Repository\EvaluationRisqueRepository;
use App\Repository\ProjetAgricoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/banque/evaluation', name: 'banque_evaluation_')]
#[IsGranted('ROLE_BANQUE')]
class EvaluationRisqueController extends AbstractController
{
    private const FLASK_API_URL = 'http://127.0.0.1:5000/evaluate';

    #[Route('/', name: 'index')]
    public function index(EvaluationRisqueRepository $repo): Response
    {
        $evaluations = $repo->findAll();

        $stats = ['faible' => 0, 'moyen' => 0, 'eleve' => 0];
        foreach ($evaluations as $e) {
            $key = $e->getNiveauRisqueNormalized();
            $stats[$key]++;
        }

        return $this->render('banque/evaluation/index.html.twig', [
            'evaluations' => $evaluations,
            'stats'       => $stats,
        ]);
    }

    #[Route('/auto-evaluate/{idProjet}', name: 'auto_evaluate', methods: ['GET'])]
    public function autoEvaluate(int $idProjet, HttpClientInterface $client): JsonResponse
    {
        try {
            $response = $client->request('GET', self::FLASK_API_URL . '/' . $idProjet, [
                'timeout' => 30,
            ]);

            $data = $response->toArray();

            return $this->json($data);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur API Flask: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/new', name: 'new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ProjetAgricoleRepository $projetRepo
    ): Response {
        $evaluation = new EvaluationRisque();
        $evaluation->setDateEvaluation(new \DateTime());

        $form = $this->createForm(EvaluationRisqueType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($evaluation);
            $em->flush();

            $this->addFlash('success', '✅ Évaluation enregistrée avec succès !');

            return $this->redirectToRoute('banque_evaluation_index');
        }

        return $this->render('banque/evaluation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(
        Request $request, 
        EvaluationRisque $evaluation, 
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(EvaluationRisqueType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Évaluation modifiée avec succès !');
            return $this->redirectToRoute('banque_evaluation_index');
        }

        return $this->render('banque/evaluation/edit.html.twig', [
            'form'       => $form->createView(),
            'evaluation' => $evaluation,
        ]);
    }

    #[Route('/{id}/show', name: 'show')]
    public function show(EvaluationRisque $evaluation): Response
    {
        return $this->render('banque/evaluation/show.html.twig', [
            'evaluation' => $evaluation,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        EvaluationRisque $evaluation, 
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $evaluation->getIdEvaluation(), $request->request->get('_token'))) {
            $em->remove($evaluation);
            $em->flush();
            $this->addFlash('success', 'Évaluation supprimée !');
        }

        return $this->redirectToRoute('banque_evaluation_index');
    }
}