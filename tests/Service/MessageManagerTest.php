<?php

namespace App\Tests\Service;

use App\Entity\Message;
use App\Entity\Conversation;
use App\Entity\Utilisateur;
use App\Entity\PieceJointe;
use App\Service\MessageManager;
use PHPUnit\Framework\TestCase;

class MessageManagerTest extends TestCase
{
    private MessageManager $manager;

    protected function setUp(): void
    {
        $this->manager = new MessageManager();
    }

    /**
     * Test : Message valide avec contenu non vide
     */
    public function testValidMessage(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Ceci est un message de test');

        $this->assertTrue($this->manager->validate($message));
    }

    /**
     * Test : Message avec contenu vide doit lever une exception
     */
    public function testMessageWithEmptyContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le contenu du message ne peut pas être vide');

        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('   '); // Contenu vide avec espaces

        $this->manager->validate($message);
    }

    /**
     * Test : Date de modification doit être postérieure à la date d'envoi
     */
    public function testDateModificationMustBeAfterDateEnvoi(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de modification doit être postérieure à la date d\'envoi');

        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Message test');
        
        $dateEnvoi = new \DateTime('2025-01-15 10:00:00');
        $dateModification = new \DateTime('2025-01-15 09:00:00'); // Antérieure!
        
        $message->setDateEnvoi($dateEnvoi);
        $message->setDateModification($dateModification);

        $this->manager->validate($message);
    }

    /**
     * Test : Date de modification valide (postérieure)
     */
    public function testValidDateModification(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Message modifié');
        
        $dateEnvoi = new \DateTime('2025-01-15 10:00:00');
        $dateModification = new \DateTime('2025-01-15 11:00:00'); // Postérieure
        
        $message->setDateEnvoi($dateEnvoi);
        $message->setDateModification($dateModification);

        $this->assertTrue($this->manager->validate($message));
        $this->assertTrue($message->isModifie());
    }

    /**
     * Test : Le nombre de pièces jointes doit correspondre
     */
    public function testNombrePiecesJointesDoitCorrespondre(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nombre de pièces jointes ne correspond pas');

        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Message avec pièce jointe');
        $message->setNbPiecesJointes(5); // Nombre incorrect

        // Pas de pièces jointes ajoutées réellement

        $this->manager->validate($message);
    }

    /**
     * Test : Marquer un message comme lu définit automatiquement la date de lecture
     */
    public function testMarquerCommeLuDefInitDateLecture(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Message non lu');

        $this->assertFalse($message->isEstLu());
        $this->assertNull($message->getDateLecture());

        $this->manager->marquerCommeLu($message);

        $this->assertTrue($message->isEstLu());
        $this->assertNotNull($message->getDateLecture());
        $this->assertInstanceOf(\DateTimeInterface::class, $message->getDateLecture());
    }

    /**
     * Test : Message lu sans date de lecture doit lever une exception
     */
    public function testMessageLuSansDateLecture(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Un message lu doit avoir une date de lecture');

        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Message test');
        $message->setEstLu(true);
        $message->setDateLecture(null); // Forcé à null pour le test

        $this->manager->validate($message);
    }

    /**
     * Test : Modifier le contenu d'un message
     */
    public function testModifierContenu(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Contenu original');

        $this->assertNull($message->getDateModification());

        $nouveauContenu = 'Contenu modifié';
        $this->manager->modifierContenu($message, $nouveauContenu);

        $this->assertEquals($nouveauContenu, $message->getContenu());
        $this->assertNotNull($message->getDateModification());
        $this->assertInstanceOf(\DateTimeInterface::class, $message->getDateModification());
    }

    /**
     * Test : Modifier avec un contenu vide doit lever une exception
     */
    public function testModifierAvecContenuVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nouveau contenu ne peut pas être vide');

        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Contenu original');

        $this->manager->modifierContenu($message, '   ');
    }

    /**
     * Test : Message peut être supprimé s'il n'est pas déjà supprimé
     */
    public function testPeutEtreSupprime(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Message à supprimer');

        $this->assertTrue($this->manager->peutEtreSupprime($message));

        $message->setEstSupprime(true);
        
        $this->assertFalse($this->manager->peutEtreSupprime($message));
    }

    /**
     * Test : Message avec pièce jointe marquée mais sans pièces réelles
     */
    public function testMessageAvecPieceJointeMarqueeMailsSansPieces(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le message est marqué avec pièce jointe mais n\'en contient aucune');

        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Message test');
        $message->setAPieceJointe(true);
        $message->setNbPiecesJointes(0);

        $this->manager->validate($message);
    }

    /**
     * Test : Calcul du temps écoulé depuis l'envoi
     */
    public function testGetTempsEcouleDepuisEnvoi(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Message test');
        
        // Message envoyé il y a 2 heures
        $dateEnvoi = new \DateTime('-2 hours');
        $message->setDateEnvoi($dateEnvoi);

        $interval = $this->manager->getTempsEcouleDepuisEnvoi($message);

        $this->assertInstanceOf(\DateInterval::class, $interval);
        $this->assertGreaterThanOrEqual(2, $interval->h);
    }

    /**
     * Test : Vérifier si un message est modifié
     */
    public function testEstModifie(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);
        $conversation = $this->createMock(Conversation::class);

        $message = new Message();
        $message->setExpediteur($utilisateur);
        $message->setConversation($conversation);
        $message->setContenu('Message original');

        // Message non modifié
        $this->assertFalse($this->manager->estModifie($message));

        // Message modifié
        $message->setDateModification(new \DateTime());
        $this->assertTrue($this->manager->estModifie($message));
    }

    /**
     * Test : Date d'envoi automatique à la création
     */
    public function testDateEnvoiAutomatique(): void
    {
        $message = new Message();

        $this->assertNotNull($message->getDateEnvoi());
        $this->assertInstanceOf(\DateTimeInterface::class, $message->getDateEnvoi());
    }
}