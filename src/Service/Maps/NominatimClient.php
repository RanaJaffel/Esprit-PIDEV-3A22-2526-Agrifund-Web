<?php

namespace App\Service\Maps;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NominatimClient
{
    public function __construct(
        private HttpClientInterface $http,
        private CacheInterface $cache
    ) {}

    public function geocode(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') return null;

        $cacheKey = 'geo_' . md5(mb_strtolower($query));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($query) {
            $item->expiresAfter(60 * 60 * 24 * 7); // 7 jours

            $response = $this->http->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'headers' => [
                    // Nominatim demande un User-Agent clair
                    'User-Agent' => 'AgriFundSmart/1.0 (contact: dev@agrifund.local)'
                ],
                'query' => [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1
                ]
            ]);

            $data = $response->toArray();

            if (empty($data)) return null;

            return [
                'lat' => (float) $data[0]['lat'],
                'lon' => (float) $data[0]['lon'],
                'display_name' => (string) ($data[0]['display_name'] ?? $query),
            ];
        });
    }
}