<?php

namespace App\Controller;

use App\Entity\OffreFinanciere;
use App\Form\OffreFinanciereType;
use App\Repository\OffreFinanciereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/offres')]
class OffreFinanciereController extends AbstractController
{
    #[Route('/', name: 'admin_offre_index', methods: ['GET'])]
    public function index(OffreFinanciereRepository $repository): Response
    {
        $offres = $repository->findAll();
        return $this->render('admin/offre/index.html.twig', [
            'offres' => $offres,
        ]);
    }

    #[Route('/new', name: 'admin_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $offre = new OffreFinanciere();
        $form = $this->createForm(OffreFinanciereType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($offre);
            $em->flush();
            $this->addFlash('success', 'Offer created successfully!');
            return $this->redirectToRoute('admin_offre_index');
        }

        return $this->render('admin/offre/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_offre_show', methods: ['GET'])]
    public function show(OffreFinanciere $offre): Response
    {
        return $this->render('admin/offre/show.html.twig', [
            'offre' => $offre,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OffreFinanciere $offre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(OffreFinanciereType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Offer updated successfully!');
            return $this->redirectToRoute('admin_offre_index');
        }

        return $this->render('admin/offre/edit.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_offre_delete', methods: ['POST'])]
    public function delete(Request $request, OffreFinanciere $offre, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $offre->getId(), $request->request->get('_token'))) {
            $em->remove($offre);
            $em->flush();
            $this->addFlash('success', 'Offer deleted successfully!');
        }

        return $this->redirectToRoute('admin_offre_index');
    }
}
