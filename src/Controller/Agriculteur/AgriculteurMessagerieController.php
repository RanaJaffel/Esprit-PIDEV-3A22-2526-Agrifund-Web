<?php

namespace App\Controller\Agriculteur;

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

#[Route('/agriculteur/messagerie')]
#[IsGranted('ROLE_AGRICULTEUR')]
class AgriculteurMessagerieController extends AbstractController
{
    #[Route('/', name: 'agriculteur_messagerie_index')]
    public function index(
        ConversationRepository $conversationRepository,
        MessageRepository $messageRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        $currentUser = $this->getUser();
        $conversations = $conversationRepository->findUserConversations($currentUser->getId());
        
        $conversationsData = [];
        foreach ($conversations as $conversation) {
            $otherUserId = $conversation->getOtherUserId($currentUser->getId());
            $otherUser = $utilisateurRepository->find($otherUserId);
            
            $conversationsData[] = [
                'conversation' => $conversation,
                'otherUser' => $otherUser,
                'lastMessage' => $conversation->getLastMessage(),
                'unreadCount' => $conversation->getUnreadMessagesCount($currentUser->getId())
            ];
        }
        
        $messagesNonLus = $messageRepository->countAllUnreadMessages($currentUser->getId());

        return $this->render('agriculteur/messagerie/conversations.html.twig', [
            'conversationsData' => $conversationsData,
            'current_user' => $currentUser,
            'messages_non_lus' => $messagesNonLus,
        ]);
    }

    #[Route('/nouvelle', name: 'agriculteur_messagerie_new')]
    public function new(UtilisateurRepository $utilisateurRepository): Response
    {
        $currentUser = $this->getUser();
        $availableUsers = $utilisateurRepository->findAvailableForMessaging($currentUser);

        return $this->render('agriculteur/messagerie/new_conversation.html.twig', [
            'available_users' => $availableUsers,
        ]);
    }

    #[Route('/conversation/{id}', name: 'agriculteur_messagerie_chat')]
    public function chat(
        Conversation $conversation,
        MessageRepository $messageRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        $currentUser = $this->getUser();

        if ($conversation->getUtilisateur1Id() !== $currentUser->getId() && 
            $conversation->getUtilisateur2Id() !== $currentUser->getId()) {
            $this->addFlash('error', 'Accès non autorisé à cette conversation.');
            return $this->redirectToRoute('agriculteur_messagerie_index');
        }

        $messageRepository->markAsRead($conversation, $currentUser->getId());
        $messages = $messageRepository->findByConversation($conversation);

        $otherUserId = $conversation->getOtherUserId($currentUser->getId());
        $otherUser = $utilisateurRepository->find($otherUserId);

        return $this->render('agriculteur/messagerie/chat.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages,
            'current_user' => $currentUser,
            'other_user' => $otherUser,
        ]);
    }

    #[Route('/start/{userId}', name: 'agriculteur_messagerie_start')]
    public function start(
        int $userId,
        UtilisateurRepository $utilisateurRepository,
        ConversationRepository $conversationRepository
    ): Response {
        $currentUser = $this->getUser();
        $otherUser = $utilisateurRepository->find($userId);

        if (!$otherUser) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('agriculteur_messagerie_index');
        }

        if (!$otherUser->getAdmin() && !$otherUser->getBanque()) {
            $this->addFlash('error', 'Vous ne pouvez contacter que les administrateurs et les banques.');
            return $this->redirectToRoute('agriculteur_messagerie_index');
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

        return $this->redirectToRoute('agriculteur_messagerie_chat', ['id' => $conversation->getId()]);
    }

    #[Route('/send/{conversationId}', name: 'agriculteur_messagerie_send', methods: ['POST'])]
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

        if (empty(trim($contenu ?? '')) && empty($files)) {
            return new JsonResponse(['error' => 'Message vide'], 400);
        }

        $message = new Message();
        $message->setConversation($conversation);
        $message->setExpediteur($currentUser);
        $message->setContenu($contenu ?: '');

        if (!empty($files)) {
            foreach ($files as $file) {
                // Vérifier si le fichier est valide
                if (!$file->isValid()) {
                    return new JsonResponse(['error' => 'Fichier invalide'], 400);
                }

                $pieceJointe = new PieceJointe();
                
                $extension = $file->guessExtension();
                if (!$extension) {
                    $extension = $file->getClientOriginalExtension();
                }
                
                $mimeType = $file->getMimeType();
                
                // Obtenir la taille AVANT de déplacer le fichier
                $fileSize = $file->getSize();
                
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
                    $pieceJointe->setTailleOctets($fileSize); // Utiliser la taille obtenue AVANT le déplacement
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
                'expediteur_nom' => $message->getExpediteur()->getNomComplet(),
                'est_lu' => $message->isEstLu(),
                'est_modifie' => false,
                'pieces_jointes' => array_map(function($pj) {
                    return [
                        'id' => $pj->getId(),
                        'nom' => $pj->getNomOriginal(),
                        'type' => $pj->getTypeFichier(),
                        'url' => '/' . $pj->getCheminFichier(),
                        'taille' => $pj->getTailleFormatee(),
                    ];
                }, $message->getPiecesJointes()->toArray()),
            ]
        ]);
    }

    #[Route('/message/{id}/edit', name: 'agriculteur_messagerie_edit', methods: ['POST'])]
    public function editMessage(
        Message $message,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $currentUser = $this->getUser();
        
        if ($message->getExpediteur()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $nouveauContenu = $request->request->get('contenu');
        
        if (empty(trim($nouveauContenu ?? ''))) {
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

    #[Route('/message/{id}/delete', name: 'agriculteur_messagerie_delete', methods: ['POST'])]
    public function deleteMessage(
        Message $message,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $currentUser = $this->getUser();
        
        if ($message->getExpediteur()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        // Correction du CSRF token
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_message' . $message->getId(), $token)) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        $message->setEstSupprime(true);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/message/{id}/csrf-token', name: 'agriculteur_messagerie_csrf_token', methods: ['GET'])]
    public function getCsrfToken(Message $message): JsonResponse
    {
        $currentUser = $this->getUser();
        
        if ($message->getExpediteur()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $token = $this->container->get('security.csrf.token_manager')
            ->getToken('delete_message' . $message->getId())
            ->getValue();

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/conversation/{id}/messages', name: 'agriculteur_messagerie_get_messages', methods: ['GET'])]
    public function getMessages(
        Conversation $conversation,
        MessageRepository $messageRepository,
        Request $request
    ): JsonResponse {
        $currentUser = $this->getUser();
        
        if ($conversation->getUtilisateur1Id() !== $currentUser->getId() && 
            $conversation->getUtilisateur2Id() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        $lastMessageId = $request->query->get('last_id', 0);
        
        $qb = $messageRepository->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->andWhere('m.estSupprime = :deleted')
            ->setParameter('conversation', $conversation)
            ->setParameter('deleted', false);
        
        if ($lastMessageId > 0) {
            $qb->andWhere('m.id > :lastId')
                ->setParameter('lastId', $lastMessageId);
        }
        
        $messages = $qb->orderBy('m.dateEnvoi', 'ASC')
            ->getQuery()
            ->getResult();

        $data = array_map(function($message) {
            return [
                'id' => $message->getId(),
                'contenu' => $message->getContenu(),
                'date' => $message->getDateEnvoi()->format('Y-m-d H:i:s'),
                'expediteur_id' => $message->getExpediteur()->getId(),
                'expediteur_nom' => $message->getExpediteur()->getNomComplet(),
                'est_lu' => $message->isEstLu(),
                'est_modifie' => $message->isModifie(),
                'pieces_jointes' => array_map(function($pj) {
                    return [
                        'id' => $pj->getId(),
                        'nom' => $pj->getNomOriginal(),
                        'type' => $pj->getTypeFichier(),
                        'url' => '/' . $pj->getCheminFichier(),
                        'taille' => $pj->getTailleFormatee(),
                    ];
                }, $message->getPiecesJointes()->toArray()),
            ];
        }, $messages);

        return new JsonResponse($data);
    }
}