<?php

namespace App\Controller\Banque;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/banque/documents')]
#[IsGranted('ROLE_BANQUE')]
class BanqueDocumentController extends AbstractController
{
    #[Route('/', name: 'banque_documents_index')]
    public function index(Request $request, DocumentRepository $documentRepository): Response
    {
        $user = $this->getUser();
        $filter = $request->query->get('filter');
        
        // Récupérer tous les documents de l'utilisateur
        $allDocuments = $documentRepository->findBy(
            ['utilisateur' => $user],
            ['dateUpload' => 'DESC']
        );
        
        // Appliquer le filtre si nécessaire
        $documents = $allDocuments;
        
        if ($filter && in_array($filter, ['valide', 'en_attente', 'rejete'])) {
            $documents = array_filter($allDocuments, function($document) use ($filter) {
                return $document->getStatut() === $filter;
            });
        }
        
        // Compter les documents par statut pour les badges
        $counts = [
            'total' => count($allDocuments),
            'valide' => count(array_filter($allDocuments, fn($d) => $d->getStatut() === 'valide')),
            'en_attente' => count(array_filter($allDocuments, fn($d) => $d->getStatut() === 'en_attente')),
            'rejete' => count(array_filter($allDocuments, fn($d) => $d->getStatut() === 'rejete')),
        ];

        return $this->render('banque/documents/index.html.twig', [
            'documents' => $documents,
            'counts' => $counts,
            'currentFilter' => $filter,
        ]);
    }

    #[Route('/add', name: 'banque_documents_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $document = new Document();
        $document->setUtilisateur($this->getUser());

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if ($file) {
                $extension = $file->guessExtension();
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^A-Za-z0-9_]/', '_', strtolower($originalFilename));

                $newFilename = 'doc_' . $this->getUser()->getId() . '_' .
                               $document->getTypeDocument() . '_' .
                               date('Ymd_His') . '.' . $extension;

                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/documents';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                // Récupérer la taille avant de déplacer
                $fileSize = $file->getSize();

                try {
                    $file->move($uploadDir, $newFilename);

                    $document->setCheminFichier('uploads/documents/' . $newFilename);
                    $document->setTaille($fileSize);
                    $document->setStatut('en_attente');

                    $em->persist($document);
                    $em->flush();

                    $this->addFlash('success', 'Document ajouté avec succès ! Il est en attente de validation.');
                    return $this->redirectToRoute('banque_documents_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier : ' . $e->getMessage());
                }
            }
        }

        return $this->render('banque/documents/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'banque_documents_edit')]
    public function edit(Document $document, Request $request, EntityManagerInterface $em): Response
    {
        if ($document->getUtilisateur()->getId() !== $this->getUser()->getId()) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('banque_documents_index');
        }

        if ($document->getStatut() === 'valide') {
            $this->addFlash('warning', 'Vous ne pouvez pas modifier un document validé.');
            return $this->redirectToRoute('banque_documents_index');
        }

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if ($file) {
                // Supprimer l'ancien fichier
                $oldFilePath = $this->getParameter('kernel.project_dir') . '/public/' . $document->getCheminFichier();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                $extension = $file->guessExtension();
                $newFilename = 'doc_' . $this->getUser()->getId() . '_' .
                               $document->getTypeDocument() . '_' .
                               date('Ymd_His') . '.' . $extension;

                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/documents';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $fileSize = $file->getSize();

                try {
                    $file->move($uploadDir, $newFilename);

                    $document->setCheminFichier('uploads/documents/' . $newFilename);
                    $document->setTaille($fileSize);
                    $document->setStatut('en_attente');

                    $em->flush();

                    $this->addFlash('success', 'Document modifié avec succès !');
                    return $this->redirectToRoute('banque_documents_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier : ' . $e->getMessage());
                }
            } else {
                $em->flush();
                $this->addFlash('success', 'Document modifié avec succès !');
                return $this->redirectToRoute('banque_documents_index');
            }
        }

        return $this->render('banque/documents/edit.html.twig', [
            'form' => $form->createView(),
            'document' => $document,
        ]);
    }

    #[Route('/{id}/download', name: 'banque_documents_download')]
    public function download(Document $document): Response
    {
        if ($document->getUtilisateur()->getId() !== $this->getUser()->getId()) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('banque_documents_index');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $document->getCheminFichier();

        if (!file_exists($filePath)) {
            $this->addFlash('error', 'Fichier introuvable.');
            return $this->redirectToRoute('banque_documents_index');
        }

        return new BinaryFileResponse($filePath);
    }

    #[Route('/{id}/delete', name: 'banque_documents_delete', methods: ['POST'])]
    public function delete(Document $document, Request $request, EntityManagerInterface $em): Response
    {
        if ($document->getUtilisateur()->getId() !== $this->getUser()->getId()) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('banque_documents_index');
        }

        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->request->get('_token'))) {
            $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $document->getCheminFichier();
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $em->remove($document);
            $em->flush();

            $this->addFlash('success', 'Document supprimé avec succès !');
        }

        return $this->redirectToRoute('banque_documents_index');
    }
}