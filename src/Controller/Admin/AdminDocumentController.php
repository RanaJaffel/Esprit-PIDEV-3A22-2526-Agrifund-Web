<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/documents')]
#[IsGranted('ROLE_ADMIN')]
class AdminDocumentController extends AbstractController
{
    #[Route('/', name: 'admin_documents_index')]
    public function index(
        Request $request,
        DocumentRepository $documentRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search');
        $statut = $request->query->get('statut');
        $type = $request->query->get('type');

        $documents = $documentRepository->searchDocuments($search, $statut, $type);

        $pagination = $paginator->paginate(
            $documents,
            $request->query->getInt('page', 1),
            15
        );

        $statistics = $documentRepository->getStatistics();

        return $this->render('admin/documents/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
            'statut' => $statut,
            'type' => $type,
            'statistics' => $statistics,
        ]);
    }

    #[Route('/{id}/review', name: 'admin_documents_review')]
    public function review(Document $document): Response
    {
        return $this->render('admin/documents/review.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/{id}/validate', name: 'admin_documents_validate', methods: ['POST'])]
    public function validate(
        Document $document,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('validate' . $document->getId(), $request->request->get('_token'))) {
            $action = $request->request->get('action');

            if ($action === 'approve') {
                $document->setStatut('valide');
                $this->addFlash('success', 'Document approuvé avec succès !');
            } elseif ($action === 'reject') {
                $document->setStatut('rejete');
                $this->addFlash('success', 'Document rejeté.');
            }

            $em->flush();
        }

        return $this->redirectToRoute('admin_documents_index');
    }

    #[Route('/{id}/download', name: 'admin_documents_download')]
    public function download(Document $document): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $document->getCheminFichier();

        if (!file_exists($filePath)) {
            $this->addFlash('error', 'Fichier introuvable.');
            return $this->redirectToRoute('admin_documents_index');
        }

        return new BinaryFileResponse($filePath);
    }

    #[Route('/{id}/delete', name: 'admin_documents_delete', methods: ['POST'])]
    public function delete(
        Document $document,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->request->get('_token'))) {
            // Supprimer le fichier
            $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $document->getCheminFichier();
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $em->remove($document);
            $em->flush();

            $this->addFlash('success', 'Document supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_documents_index');
    }
}