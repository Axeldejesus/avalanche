<?php

namespace App\Controller;

use App\Service\IgdbApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route('/', name: 'game_list')]
    public function index(Request $request, IgdbApiClient $igdbApiClient): Response
    {
        $search = $request->query->get('search', '');
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 10;
        $ffArtworks = $igdbApiClient->getFinalFantasyArtworks(15);
    
        if (!empty($search)) {
            $games = $igdbApiClient->searchGames($search, $limit, $page);
        } else {
            $games = $igdbApiClient->getUpcomingGames($limit, $page);
        }
        return $this->render('game/index.html.twig', [
            'upcominggames' => $games,
            'search' => $search,
            'page' => $page,
            'limit' => $limit,
            'ffArtworks' => $ffArtworks
        ]);
    }
    
    #[Route('/game/{id}', name: 'game_details')]
    public function details(Request $request, IgdbApiClient $igdbApiClient, int $id): Response
    {
        $game = $igdbApiClient->getGame($id);
    
        return $this->render('game/details.html.twig', [
            'game' => $game,
        ]);
    }
}