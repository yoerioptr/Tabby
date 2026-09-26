<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\RankingProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RankingController extends AbstractController
{
    public function __construct(private readonly RankingProvider $rankingProvider)
    {
    }

    #[Route('/ranking', name: 'app_ranking')]
    public function index(): Response
    {
        return $this->render('ranking.html.twig', [
            'title' => 'Ranking',
            'divisions' => $this->rankingProvider->forCurrentSeason(),
        ]);
    }
}
