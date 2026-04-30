<?php

declare(strict_types=1);

namespace App\Service\Crypto;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BinanceLeaderboardService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(RAPIDAPI_KEY)%')]
        private string $rapidApiKey
    ) {
    }

    public function getLeaderboard(): array
    {
        $response = $this->httpClient->request('GET', 'https://binance-futures-leaderboard1.p.rapidapi.com/v2/searchLeaderboard', [
            'query' => [
                'isTrader' => 'false',
                'periodType' => 'WEEKLY',
                'isShared' => 'true',
                'statisticsType' => 'PNL',
                'tradeType' => 'PERPETUAL',
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'binance-futures-leaderboard1.p.rapidapi.com',
                'x-rapidapi-key' => $this->rapidApiKey,
            ],
        ]);

        return $response->toArray();
    }
}
