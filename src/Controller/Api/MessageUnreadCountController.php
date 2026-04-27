<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Utilisateur;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MessageUnreadCountController extends AbstractController
{
    #[Route('/api/messages/unread-count', name: 'api_messages_unread_count', methods: ['GET'])]
    public function __invoke(MessageRepository $messageRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Utilisateur || $user->getId() === null) {
            return new JsonResponse(['count' => 0], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'count' => $messageRepository->countAllUnreadMessages($user->getId()),
        ]);
    }
}
