<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class IgdbApiService
{
    private $httpClient;
    private $cache;
    private $clientId;
    private $secretKey;
    private $accessToken;

    public function __construct(
        HttpClientInterface $httpClient,
        CacheInterface $cache,
        string $clientId,
        string $secretKey
    ) {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->clientId = $clientId;
        $this->secretKey = $secretKey;

        $this->accessToken = $this->fetchAccessToken();
    }

    private function fetchAccessToken(): string
    {
        return $this->cache->get('igdb_access_token', function (ItemInterface $item) {
            $item->expiresAfter(3600); // Le jeton expire après 1 heure
            $response = $this->httpClient->request('POST', 'https://id.twitch.tv/oauth2/token', [
                'query' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->secretKey,
                    'grant_type' => 'client_credentials',
                ],
            ]);
            $data = $response->toArray();
            return $data['access_token'];
        });
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}