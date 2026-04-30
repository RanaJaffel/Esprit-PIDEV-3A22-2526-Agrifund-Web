<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessageUnreadCountController extends AbstractController
{
    #[Route('/api/messages/unread-count', name: 'api_messages_unread_count', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(MessageRepository $messageRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof Utilisateur || $user->getId() === null) {
            throw $this->createAccessDeniedException();
        }

        return new JsonResponse([
            'count' => $messageRepository->countAllUnreadMessages($user->getId()),
        ]);
    }
}
