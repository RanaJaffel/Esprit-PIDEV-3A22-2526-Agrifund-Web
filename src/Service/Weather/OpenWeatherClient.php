<?php

namespace App\Service\Weather;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenWeatherClient
{
    public function __construct(
        private HttpClientInterface $http,
        private CacheInterface $cache,
        private string $apiKey,
        private string $baseUrl
    ) {}

    public function getCurrent(float $lat, float $lon): array
    {
        $cacheKey = sprintf('meteo_current_%.4f_%.4f', $lat, $lon);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($lat, $lon) {
            $item->expiresAfter(600); // 10 minutes

            $url = $this->baseUrl.'/weather';
            $response = $this->http->request('GET', $url, [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ]
            ]);

            return $response->toArray();
        });
    }

    public function getForecast(float $lat, float $lon): array
    {
        $cacheKey = sprintf('meteo_forecast_%.4f_%.4f', $lat, $lon);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($lat, $lon) {
            $item->expiresAfter(1800); // 30 minutes

            $url = $this->baseUrl.'/forecast';
            $response = $this->http->request('GET', $url, [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ]
            ]);

            return $response->toArray();
        });
    }
    public function getCurrentByCity(string $city): array
{
    $city = trim($city);
    $cacheKey = 'meteo_current_city_' . md5(mb_strtolower($city));

    return $this->cache->get($cacheKey, function (ItemInterface $item) use ($city) {
        $item->expiresAfter(600); // 10 min

        $url = $this->baseUrl . '/weather';
        $response = $this->http->request('GET', $url, [
            'query' => [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'lang' => 'fr',
            ]
        ]);

        return $response->toArray();
    });
}

public function getForecastByCity(string $city): array
{
    $city = trim($city);
    $cacheKey = 'meteo_forecast_city_' . md5(mb_strtolower($city));

    return $this->cache->get($cacheKey, function (ItemInterface $item) use ($city) {
        $item->expiresAfter(1800); // 30 min

        $url = $this->baseUrl . '/forecast';
        $response = $this->http->request('GET', $url, [
            'query' => [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'lang' => 'fr',
            ]
        ]);

        return $response->toArray();
    });
}
}