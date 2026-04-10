<?php

namespace App\Controller\Banque;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\PieceJointe;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/banque/messagerie')]
#[IsGranted('ROLE_BANQUE')]
class BanqueMessagerieController extends AbstractController
{
    #[Route('/', name: 'banque_messagerie_index')]
    public function index(
        ConversationRepository $conversationRepository,
        MessageRepository $messageRepository
    ): Response {
        $currentUser = $this->getUser();
        $conversations = $conversationRepository->findUserConversations($currentUser->getId());
        
        $messagesNonLus = $messageRepository->countAllUnreadMessages($currentUser->getId());

        return $this->render('banque/messagerie/conversations.html.twig', [
            'conversations' => $conversations,
            'current_user' => $currentUser,
            'messages_non_lus' => $messagesNonLus,
        ]);
    }

    #[Route('/nouvelle', name: 'banque_messagerie_new')]
    public function new(UtilisateurRepository $utilisateurRepository): Response
    {
        $currentUser = $this->getUser();
        
        // Banque peut contacter uniquement les admins et les agriculteurs
        $availableUsers = $utilisateurRepository->findAvailableForMessaging($currentUser);

        return $this->render('banque/messagerie/new_conversation.html.twig', [
            'available_users' => $availableUsers,
        ]);
    }

    #[Route('/conversation/{id}', name: 'banque_messagerie_chat')]
    public function chat(
        Conversation $conversation,
        MessageRepository $messageRepository
    ): Response {
        $currentUser = $this->getUser();
        
        if ($conversation->getUtilisateur1Id() !== $currentUser->getId() && 
            $conversation->getUtilisateur2Id() !== $currentUser->getId()) {
            $this->addFlash('error', 'Accès non autorisé à cette conversation.');
            return $this->redirectToRoute('banque_messagerie_index');
        }

        $messageRepository->markAsRead($conversation, $currentUser->getId());
        $messages = $messageRepository->findByConversation($conversation);

        return $this->render('banque/messagerie/chat.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/start/{userId}', name: 'banque_messagerie_start')]
    public function start(
        int $userId,
        UtilisateurRepository $utilisateurRepository,
        ConversationRepository $conversationRepository
    ): Response {
        $currentUser = $this->getUser();
        $otherUser = $utilisateurRepository->find($userId);

        if (!$otherUser) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('banque_messagerie_index');
        }

        // Vérifier que c'est un admin ou un agriculteur
        if (!$otherUser->getAdmin() && !$otherUser->getAgriculteur()) {
            $this->addFlash('error', 'Vous ne pouvez contacter que les administrateurs et les agriculteurs.');
            return $this->redirectToRoute('banque_messagerie_index');
        }

        $conversation = $conversationRepository->findConversationBetweenUsers(
            $currentUser->getId(),
            $otherUser->getId()
        );

        if (!$conversation) {
            $conversation = $conversationRepository->findOrCreateConversation(
                $currentUser->getId(),
                $otherUser->getId()
            );
        }

        return $this->redirectToRoute('banque_messagerie_chat', ['id' => $conversation->getId()]);
    }

    #[Route('/send/{conversationId}', name: 'banque_messagerie_send', methods: ['POST'])]
    public function send(
        int $conversationId,
        Request $request,
        ConversationRepository $conversationRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $conversation = $conversationRepository->find($conversationId);
        
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation non trouvée'], 404);
        }

        $currentUser = $this->getUser();
        
        if ($conversation->getUtilisateur1Id() !== $currentUser->getId() && 
            $conversation->getUtilisateur2Id() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        $contenu = $request->request->get('contenu');
        $files = $request->files->get('files', []);

        if (empty($contenu) && empty($files)) {
            return new JsonResponse(['error' => 'Message vide'], 400);
        }

        $message = new Message();
        $message->setConversation($conversation);
        $message->setExpediteur($currentUser);
        $message->setContenu($contenu ?: '');

        if (!empty($files)) {
            foreach ($files as $file) {
                $pieceJointe = new PieceJointe();
                
                $extension = $file->guessExtension();
                $mimeType = $file->getMimeType();
                
                $typeFichier = 'autre';
                if (str_starts_with($mimeType, 'image/')) {
                    $typeFichier = 'image';
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $typeFichier = 'video';
                } elseif (str_starts_with($mimeType, 'audio/')) {
                    $typeFichier = 'audio';
                } elseif (in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'])) {
                    $typeFichier = 'document';
                }
                
                $newFilename = 'msg_' . uniqid() . '_' . $typeFichier . '_' . date('Ymd_His') . '.' . $extension;
                
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/messagerie/';
                if ($typeFichier === 'image') {
                    $uploadDir .= 'images/';
                } else {
                    $uploadDir .= 'files/';
                }
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                try {
                    $file->move($uploadDir, $newFilename);
                    
                    $pieceJointe->setMessage($message);
                    $pieceJointe->setTypeFichier($typeFichier);
                    $pieceJointe->setNomOriginal($file->getClientOriginalName());
                    $pieceJointe->setNomStockage($newFilename);
                    $pieceJointe->setCheminFichier(str_replace($this->getParameter('kernel.project_dir') . '/public/', '', $uploadDir . $newFilename));
                    $pieceJointe->setTailleOctets($file->getSize());
                    $pieceJointe->setExtension($extension);
                    $pieceJointe->setMimeType($mimeType);
                    
                    $message->addPieceJointe($pieceJointe);
                    $em->persist($pieceJointe);
                } catch (\Exception $e) {
                    return new JsonResponse(['error' => 'Erreur lors de l\'upload: ' . $e->getMessage()], 500);
                }
            }
        }

        $em->persist($message);
        $conversation->setDerniereActivite(new \DateTime());
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'contenu' => $message->getContenu(),
                'date' => $message->getDateEnvoi()->format('Y-m-d H:i:s'),
                'expediteur_id' => $message->getExpediteur()->getId(),
                'pieces_jointes' => array_map(function($pj) {
                    return [
                        'id' => $pj->getId(),
                        'nom' => $pj->getNomOriginal(),
                        'type' => $pj->getTypeFichier(),
                        'url' => '/' . $pj->getCheminFichier(),
                    ];
                }, $message->getPiecesJointes()->toArray()),
            ]
        ]);
    }

    #[Route('/message/{id}/edit', name: 'banque_messagerie_edit', methods: ['POST'])]
    public function editMessage(
        Message $message,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($message->getExpediteur()->getId() !== $this->getUser()->getId()) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $nouveauContenu = $request->request->get('contenu');
        
        if (empty($nouveauContenu)) {
            return new JsonResponse(['error' => 'Le message ne peut pas être vide'], 400);
        }

        $message->setContenu($nouveauContenu);
        $message->setDateModification(new \DateTime());
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'contenu' => $message->getContenu(),
                'date_modification' => $message->getDateModification()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/message/{id}/delete', name: 'banque_messagerie_delete', methods: ['POST'])]
    public function deleteMessage(
        Message $message,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($message->getExpediteur()->getId() !== $this->getUser()->getId()) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        if (!$this->isCsrfTokenValid('delete_message' . $message->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        $message->setEstSupprime(true);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}