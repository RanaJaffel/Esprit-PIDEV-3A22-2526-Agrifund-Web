<?php

namespace App\Service;

use App\Entity\Message;

class MessageManager
{
    /**
     * Valide les données d'un message
     */
    public function validate(Message $message): bool
    {
        // Règle 1 : Le contenu ne peut pas être vide
        if (empty(trim($message->getContenu()))) {
            throw new \InvalidArgumentException('Le contenu du message ne peut pas être vide');
        }

        // Règle 2 : La date de modification doit être postérieure à la date d'envoi
        if ($message->getDateModification() !== null) {
            if ($message->getDateModification() <= $message->getDateEnvoi()) {
                throw new \InvalidArgumentException('La date de modification doit être postérieure à la date d\'envoi');
            }
        }

        // Règle 3 : Le nombre de pièces jointes doit correspondre
        $nbPiecesReelles = $message->getPiecesJointes()->count();
        if ($message->getNbPiecesJointes() !== $nbPiecesReelles) {
            throw new \InvalidArgumentException('Le nombre de pièces jointes ne correspond pas');
        }

        // Règle 4 : Si aPieceJointe est true, il doit y avoir au moins une pièce
        if ($message->isAPieceJointe() && $nbPiecesReelles === 0) {
            throw new \InvalidArgumentException('Le message est marqué avec pièce jointe mais n\'en contient aucune');
        }

        // Règle 5 : Si le message est lu, la date de lecture doit être définie
        if ($message->isEstLu() && $message->getDateLecture() === null) {
            throw new \InvalidArgumentException('Un message lu doit avoir une date de lecture');
        }

        return true;
    }

    /**
     * Marque un message comme lu
     */
    public function marquerCommeLu(Message $message): void
    {
        if (!$message->isEstLu()) {
            $message->setEstLu(true);
            // La date de lecture est automatiquement définie dans setEstLu()
        }
    }

    /**
     * Modifie le contenu d'un message
     */
    public function modifierContenu(Message $message, string $nouveauContenu): void
    {
        if (empty(trim($nouveauContenu))) {
            throw new \InvalidArgumentException('Le nouveau contenu ne peut pas être vide');
        }

        $message->setContenu($nouveauContenu);
        $message->setDateModification(new \DateTime());
    }

    /**
     * Vérifie si un message peut être supprimé
     */
    public function peutEtreSupprime(Message $message): bool
    {
        // Un message peut être supprimé s'il n'est pas déjà supprimé
        return !$message->isEstSupprime();
    }

    /**
     * Calcule le temps écoulé depuis l'envoi
     */
    public function getTempsEcouleDepuisEnvoi(Message $message): \DateInterval
    {
        $maintenant = new \DateTime();
        return $message->getDateEnvoi()->diff($maintenant);
    }

    /**
     * Vérifie si le message a été modifié
     */
    public function estModifie(Message $message): bool
    {
        return $message->isModifie();
    }
}