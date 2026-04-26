<?php

namespace App\Service;

use App\Entity\DecisionFinanciere;

class DecisionFinanciereValidator
{
    public function validate(DecisionFinanciere $decision): bool
    {
        $statut = $decision->getStatut();

        if (!in_array($statut, ['approuve', 'refuse', 'en_attente'], true)) {
            throw new \InvalidArgumentException('Le statut doit être approuve, refuse ou en_attente.');
        }

        if ($statut === 'refuse' && empty($decision->getJustification())) {
            throw new \InvalidArgumentException('Une justification est obligatoire en cas de refus.');
        }

        if ($statut === 'approuve') {
            $evaluation = $decision->getEvaluation();
            if (!$evaluation || $evaluation->getScoreGlobal() < 50) {
                throw new \InvalidArgumentException('Une décision approuvée nécessite une évaluation avec un score >= 50.');
            }
        }

        return true;
    }
}