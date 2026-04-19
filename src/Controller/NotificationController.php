<?php

namespace App\Controller;

use App\Repository\CapteurRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    private function denyIfProjectNotOwned(int $idproject, CapteurRepository $capteurRepo): void
    {
        // Admin voit tout
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId')) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $count = $capteurRepo->count([
            'idproject' => $idproject,
            'idUser' => (int) $user->getId(),
        ]);

        if ($count === 0) {
            throw $this->createAccessDeniedException("Accès refusé : projet non autorisé.");
        }
    }

    #[Route('/projet/{idproject}', name: 'notif_project', methods: ['GET'])]
    public function byProject(
        int $idproject,
        NotificationRepository $repo,
        CapteurRepository $capteurRepo
    ): Response {
        $this->denyIfProjectNotOwned($idproject, $capteurRepo);

        $notifs = $repo->findBy(
            ['projetId' => $idproject],
            ['createdAt' => 'DESC']
        );

        return $this->render('notification/index.html.twig', [
            'notifs' => $notifs,
            'idproject' => $idproject,
        ]);
    }

    #[Route('/{id}/read', name: 'notif_read', methods: ['POST'])]
    public function read(
        int $id,
        NotificationRepository $repo,
        CapteurRepository $capteurRepo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $n = $repo->find($id);
        if (!$n) {
            throw $this->createNotFoundException('Notification introuvable.');
        }

        // Sécurité : vérifier accès au projet de la notification
        $this->denyIfProjectNotOwned($n->getProjetId(), $capteurRepo);

        if (!$this->isCsrfTokenValid('read' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF invalide.');
        }

        $n->markRead();
        $em->flush();

        // Retourne vers la page notifications du projet
        return $this->redirectToRoute('notif_project', [
            'idproject' => $n->getProjetId(),
        ]);
    }
}