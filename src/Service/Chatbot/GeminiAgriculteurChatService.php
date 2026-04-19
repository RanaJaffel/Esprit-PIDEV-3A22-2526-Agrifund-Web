<?php

namespace App\Service\Chatbot;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiAgriculteurChatService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(string:GEMINI_API_KEY)%')]
        private readonly string $apiKey,
        #[Autowire('%env(default::GEMINI_MODEL)%')]
        private readonly ?string $model = null
    ) {
    }

    public function askForOfferOrProduct(string $question, string $contextType, string $title, string $summary): string
    {
        if (trim($this->apiKey) === '') {
            throw new \RuntimeException('La cle API Gemini est absente. Configurez GEMINI_API_KEY dans .env.');
        }

        $configuredModel = trim((string) ($this->model ?? ''));
        $modelCandidates = $this->buildModelCandidates($configuredModel);

        $contextLabel = $contextType === 'offre' ? 'offre financiere' : 'produit financier';

        $prompt = sprintf(
            "Tu es un assistant AgriFund pour agriculteur. Reponds uniquement en francais simple et clair.\n" .
            "Role: expliquer uniquement les offres et produits financiers (prix, conditions, taux, eligibilite, etapes).\n" .
            "Si la question sort de ce perimetre, refuse poliment et recentre sur l'offre/produit.\n" .
            "Sois concis (max 8 lignes), pratique, et donne 1 recommandation concrete a la fin.\n\n" .
            "Contexte courant:\n" .
            "- Type: %s\n" .
            "- Titre: %s\n" .
            "- Resume: %s\n\n" .
            "Question utilisateur: %s",
            $contextLabel,
            $title,
            $summary,
            $question
        );

        $lastError = null;
        $quotaError = null;

        foreach ($modelCandidates as $model) {
            try {
                $data = $this->requestGemini($model, $prompt);

                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (is_string($text) && trim($text) !== '') {
                    return trim($text);
                }

                $blockReason = $data['promptFeedback']['blockReason'] ?? null;
                if (is_string($blockReason) && $blockReason !== '') {
                    throw new \RuntimeException('Reponse bloquee par Gemini: ' . $blockReason);
                }

                throw new \RuntimeException('Gemini n\'a pas retourne de reponse exploitable.');
            } catch (\RuntimeException $e) {
                $lower = mb_strtolower($e->getMessage());
                if (str_contains($lower, 'quota') || str_contains($lower, 'resource_exhausted') || str_contains($lower, 'http 429')) {
                    $quotaError = $e;
                }
                $lastError = $e;
            }
        }

        if ($quotaError !== null) {
            throw $quotaError;
        }

        if ($lastError !== null) {
            throw $lastError;
        }

        throw new \RuntimeException('Gemini indisponible pour le moment.');
    }

    /**
     * Equivalent Symfony de la methode Java genererTexte(prompt).
     */
    public function genererTexte(string $prompt): string
    {
        $prompt = trim($prompt);
        if ($prompt === '') {
            return 'Erreur lors de la generation: prompt vide.';
        }

        try {
            return $this->generateWithPrompt($prompt);
        } catch (\Throwable $e) {
            return 'Erreur lors de la generation: ' . $e->getMessage();
        }
    }

    /**
     * Equivalent Symfony de la methode Java conseillerProduit(...).
     */
    public function conseillerProduit(string $nomProduit, string $typeFinancement, float $tauxInteret): string
    {
        $prompt = sprintf(
            "En tant qu'expert en financement agricole, donne un conseil concis (3-4 phrases) sur le produit financier suivant:\n" .
            "- Nom: %s\n" .
            "- Type: %s\n" .
            "- Taux d'interet: %.2f%%\n" .
            "Reponds en francais.",
            $nomProduit,
            $typeFinancement,
            $tauxInteret
        );

        return $this->genererTexte($prompt);
    }

    /**
     * @throws \RuntimeException
     */
    public function generateWithPrompt(string $prompt): string
    {
        if (trim($this->apiKey) === '') {
            throw new \RuntimeException('La cle API Gemini est absente. Configurez GEMINI_API_KEY dans .env.');
        }

        $configuredModel = trim((string) ($this->model ?? ''));
        $modelCandidates = $this->buildModelCandidates($configuredModel);

        $lastError = null;
        $quotaError = null;

        foreach ($modelCandidates as $model) {
            try {
                $data = $this->requestGemini($model, $prompt);
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if (is_string($text) && trim($text) !== '') {
                    return trim($text);
                }

                $blockReason = $data['promptFeedback']['blockReason'] ?? null;
                if (is_string($blockReason) && $blockReason !== '') {
                    throw new \RuntimeException('Reponse bloquee par Gemini: ' . $blockReason);
                }

                throw new \RuntimeException('Gemini n\'a pas retourne de reponse exploitable.');
            } catch (\RuntimeException $e) {
                $lower = mb_strtolower($e->getMessage());
                if (str_contains($lower, 'quota') || str_contains($lower, 'resource_exhausted') || str_contains($lower, 'http 429')) {
                    $quotaError = $e;
                }
                $lastError = $e;
            }
        }

        if ($quotaError !== null) {
            throw $quotaError;
        }

        if ($lastError !== null) {
            throw $lastError;
        }

        throw new \RuntimeException('Gemini indisponible pour le moment.');
    }

    /**
     * @return array<string, mixed>
     */
    private function requestGemini(string $model, string $prompt): array
    {
        $model = preg_replace('#^models/#', '', trim($model)) ?? trim($model);

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            rawurlencode($model),
            rawurlencode($this->apiKey)
        );

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 350,
                    ],
                ],
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('Erreur reseau Gemini: ' . $e->getMessage(), 0, $e);
        }

        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);

        if ($statusCode >= 400) {
            $apiMessage = is_array($data) ? (string) ($data['error']['message'] ?? '') : '';
            $apiStatus = is_array($data) ? (string) ($data['error']['status'] ?? '') : '';

            $parts = array_filter([
                sprintf('HTTP %d', $statusCode),
                $apiStatus,
                $apiMessage,
            ]);

            throw new \RuntimeException('Gemini API erreur: ' . implode(' - ', $parts));
        }

        if (!is_array($data)) {
            throw new \RuntimeException('Reponse Gemini invalide (format inattendu).');
        }

        return $data;
    }

    /**
     * @return string[]
     */
    private function buildModelCandidates(string $configuredModel): array
    {
        $candidates = [];

        if ($configuredModel !== '') {
            $candidates[] = preg_replace('#^models/#', '', $configuredModel) ?? $configuredModel;
        }

        $candidates[] = 'gemini-2.0-flash';
        $candidates[] = 'gemini-2.0-flash-lite';
        $candidates[] = 'gemini-2.5-flash';
        $candidates[] = 'gemini-2.5-flash-lite';

        foreach ($this->listGenerateContentModels() as $modelName) {
            $candidates[] = $modelName;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * @return string[]
     */
    private function listGenerateContentModels(): array
    {
        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models?key=%s',
            rawurlencode($this->apiKey)
        );

        try {
            $response = $this->httpClient->request('GET', $url);
            if ($response->getStatusCode() >= 400) {
                return [];
            }

            $data = $response->toArray(false);
        } catch (\Throwable) {
            return [];
        }

        if (!is_array($data) || !isset($data['models']) || !is_array($data['models'])) {
            return [];
        }

        $models = [];
        foreach ($data['models'] as $model) {
            if (!is_array($model)) {
                continue;
            }

            $methods = $model['supportedGenerationMethods'] ?? null;
            if (!is_array($methods) || !in_array('generateContent', $methods, true)) {
                continue;
            }

            $name = isset($model['name']) ? (string) $model['name'] : '';
            $name = preg_replace('#^models/#', '', trim($name)) ?? trim($name);
            if ($name !== '') {
                $models[] = $name;
            }
        }

        return $models;
    }
}
