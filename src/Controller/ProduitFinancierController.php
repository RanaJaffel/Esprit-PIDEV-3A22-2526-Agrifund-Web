<?php

namespace App\Controller;

use App\Entity\ProduitFinancier;
use App\Form\ProduitFinancierType;
use App\Repository\ProduitFinancierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/produits')]
class ProduitFinancierController extends AbstractController
{
    #[Route('/', name: 'admin_produit_index', methods: ['GET'])]
    public function index(ProduitFinancierRepository $repository): Response
    {
        $produits = $repository->findAll();
        return $this->render('admin/produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/new', name: 'admin_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new ProduitFinancier();
        $form = $this->createForm(ProduitFinancierType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Product created successfully!');
            return $this->redirectToRoute('admin_produit_index');
        }

        return $this->render('admin/produit/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_produit_show', methods: ['GET'])]
    public function show(ProduitFinancier $produit): Response
    {
        return $this->render('admin/produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProduitFinancier $produit, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitFinancierType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Product updated successfully!');
            return $this->redirectToRoute('admin_produit_index');
        }

        return $this->render('admin/produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_produit_delete', methods: ['POST'])]
    public function delete(Request $request, ProduitFinancier $produit, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Product deleted successfully!');
        }

        return $this->redirectToRoute('admin_produit_index');
    }
}
