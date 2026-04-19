<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class IoTAiClient
{
    public function __construct(
        private HttpClientInterface $http
    ) {}

    public function detectAnomaly(
        int $projectId,
        int $sensorId,
        string $typeMesure,
        array $historyValues,
        float $currentValue
    ): ?array {

        $history = array_map(
            fn($v) => ['value' => (float)$v],
            $historyValues
        );

        try {
            $response = $this->http->request(
                'POST',
                'http://127.0.0.1:5001/detect-anomaly',
                [
                    'json' => [
                        'projectId' => $projectId,
                        'sensorId' => $sensorId,
                        'typeMesure' => $typeMesure,
                        'history' => $history,
                        'currentValue' => $currentValue
                    ],
                    'timeout' => 5,
                ]
            );

            return $response->toArray(false);

        } catch (\Exception $e) {
            return null;
        }
    }
}