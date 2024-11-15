<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;

class IgdbApiClient
{
    private $httpClient;
    private $clientId;
    private $accessToken;
    private $cache;
    private $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        CacheInterface $cache,
        IgdbApiService $authService,
        GameTranslator $translator,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->clientId = $authService->getClientId();
        $this->accessToken = $authService->getAccessToken();
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function searchGames(string $search = '', int $limit = 10, int $page = 1): array
{
    try {
        
        $offset = ($page - 1) * $limit;
        
        $cacheKey = 'game_search_' . md5($search . $limit . $page);
        $games = $this->cache->get($cacheKey, function (ItemInterface $item) use ($search, $limit, $offset) {
            $item->expiresAfter(3600);

            $body = "fields name,id,summary,cover.url; limit {$limit}; offset {$offset};";
            if ($search) {
                $body .= " search \"{$search}\";";
            }

            $response = $this->httpClient->request('POST', 'https://api.igdb.com/v4/games', [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
                'body' => $body,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Erreur API IGDB: ' . $response->getStatusCode());
            }

            $games = $response->toArray();

            foreach ($games as &$game) {
                if (isset($game['cover']['url'])) {
                    $game['cover']['url'] = str_replace('t_thumb', 't_cover_big', $game['cover']['url']);
                }
                try {
                    if (isset($game['summary']) && !empty($game['summary'])) {
                        $game['summary'] = $this->translator->translateText($game['summary']);
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Erreur de traduction pour le jeu: ' . ($game['name'] ?? 'Nom inconnu'), [
                        'exception' => $e,
                        'summary' => $game['summary'] ?? 'Pas de résumé'
                    ]);
                    $game['summary'] = 'Traduction non disponible';
                }
            }

            return $games;
        });

        return $games;
    } catch (\Exception $e) {
        $this->logger->error('Erreur lors de la recherche de jeux: ' . $e->getMessage());
        return [];
    }
}
    
    public function getGame(int $id): array
    {
        try {
            $cacheKey = 'game_details_' . $id;
            $game = $this->cache->get($cacheKey, function (ItemInterface $item) use ($id) {
                $item->expiresAfter(3600);
                $response = $this->httpClient->request('POST', 'https://api.igdb.com/v4/games', [
                    'headers' => [
                        'Client-ID' => $this->clientId,
                        'Authorization' => "Bearer {$this->accessToken}",
                    ],
                    'body' => "fields name,summary,storyline,cover.url,screenshots.url,
                        artworks.url,videos.*,rating,rating_count,
                        genres.name,game_modes.name,
                        platforms.name,platforms.platform_logo.url,
                        first_release_date,
                        release_dates.date,release_dates.platform.name,
                        involved_companies.company.name,involved_companies.developer,
                        involved_companies.publisher,dlcs.name,
                        similar_games.name,similar_games.cover.url,
                        websites.*,age_ratings.*,total_rating,
                        game_engines.name,player_perspectives.name;
                        where id = {$id};",
                ]);

                $games = $response->toArray();
                if (empty($games)) {
                    throw new NotFoundHttpException('Jeu non trouvé');
                }

                $game = $games[0];

                // Traduction sécurisée
                try {
                    if (isset($game['summary']) && !empty($game['summary'])) {
                        $game['summary'] = $this->translator->translateText($game['summary']);
                    }
                    if (isset($game['storyline']) && !empty($game['storyline'])) {
                        $game['storyline'] = $this->translator->translateText($game['storyline']);
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Erreur de traduction pour le jeu: ' . ($game['name'] ?? 'Nom inconnu'), [
                        'exception' => $e
                    ]);
                }

                return $game;
            });

            return $game;
        } catch (NotFoundHttpException $e) {
            $this->logger->warning('Jeu non trouvé', ['id' => $id]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération du jeu', ['id' => $id, 'message' => $e->getMessage()]);
            throw new \Exception('Erreur lors de la récupération du jeu');
        }
    }

    public function getGameArtworks(int $gameId): array
    {
        $cacheKey = 'game_artworks_' . $gameId;
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($gameId) {
            $item->expiresAfter(3600);
            $response = $this->httpClient->request('POST', 'https://api.igdb.com/v4/artworks', [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
                'body' => "fields *; where game = {$gameId};",
            ]);
            return $response->toArray();
        });
    }

    public function getGameVideos(int $gameId): array
    {
        $cacheKey = 'game_videos_' . $gameId;
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($gameId) {
            $item->expiresAfter(3600);
            $response = $this->httpClient->request('POST', 'https://api.igdb.com/v4/game_videos', [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
                'body' => "fields video_id,name; where game = {$gameId};",
            ]);
            return $response->toArray();
        });
    }
    
    public function getUpcomingGames(int $limit = 10, int $page = 1): array
{
    try {
        $cacheKey = 'upcoming_games_' . $limit . '_page_' . $page;
        $games = $this->cache->get($cacheKey, function (ItemInterface $item) use ($limit, $page) {
            $item->expiresAfter(3600);
    
            $currentTime = time();
            $offset = ($page - 1) * $limit;
    
            $response = $this->httpClient->request('POST', 'https://api.igdb.com/v4/games', [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
                'body' => "fields name, id, summary, cover.url, first_release_date;
                           where first_release_date >= {$currentTime}
                           & status != null;
                           sort first_release_date asc;
                           limit {$limit}; offset {$offset};"
            ]);
    
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Erreur API IGDB: ' . $response->getStatusCode());
            }
    
            $games = $response->toArray();
            foreach ($games as &$game) {
                if (isset($game['first_release_date'])) {
                    $game['release_date'] = date('d/m/Y', $game['first_release_date']);
                }
                
                try {
                    if (isset($game['summary']) && !empty($game['summary'])) {
                        $game['summary'] = $this->translator->translateText($game['summary']);
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Erreur de traduction: ' . $e->getMessage());
                    $game['summary'] = 'Résumé non disponible';
                }
    
                if (isset($game['cover']['url'])) {
                    $game['cover']['url'] = str_replace('t_thumb', 't_cover_big', $game['cover']['url']);
                }
            }
    
            return $games;
        });
    
        return $games;
    } catch (\Exception $e) {
        $this->logger->error('Erreur lors de la récupération des jeux: ' . $e->getMessage());
        return [];
    }
}

public function getFinalFantasyArtworks(int $limit = 100): array
{
    try {
        $cacheKey = 'ff_artworks_' . $limit . '_' . date('Y-m-d_H');
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($limit) {
            $item->expiresAfter(3600);

            // Récupérer les jeux Final Fantasy principaux
            $gamesResponse = $this->httpClient->request('POST', 'https://api.igdb.com/v4/games', [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
                'body' => "fields id,name;
                          where name ~ *\"Final Fantasy\"* 
                          & category = 0 
                          & version_parent = null
                          & artworks != null;
                          limit 100;"
            ]);

            $games = $gamesResponse->toArray();
            $this->logger->info('FF Games found:', ['count' => count($games)]);

            if (empty($games)) {
                return [];
            }

            $gameIds = array_column($games, 'id');
            
            // Récupérer les artworks
            $artworksResponse = $this->httpClient->request('POST', 'https://api.igdb.com/v4/artworks', [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
                'body' => "fields *;
                          where game = (" . implode(',', $gameIds) . ");
                          limit 500;"
            ]);

            $artworks = $artworksResponse->toArray();
            $this->logger->info('Artworks found:', ['count' => count($artworks)]);

            // Transformer les URLs
            $transformedArtworks = array_map(function($artwork) {
                if (isset($artwork['image_id'])) {
                    return 'https://images.igdb.com/igdb/image/upload/t_1080p/' . $artwork['image_id'] . '.jpg';
                }
                return null;
            }, $artworks);

            // Filtrer les URLs null
            $transformedArtworks = array_filter($transformedArtworks);
            
            // Mélanger aléatoirement les artworks
            shuffle($transformedArtworks);
            
            // Limiter le nombre d'artworks
            $transformedArtworks = array_slice($transformedArtworks, 0, $limit);

            $this->logger->info('Final transformed artworks:', ['count' => count($transformedArtworks)]);

            return array_values($transformedArtworks);
        });
    } catch (\Exception $e) {
        $this->logger->error('Erreur lors de la récupération des artworks FF:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return [];
    }
}
}