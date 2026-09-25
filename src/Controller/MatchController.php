<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MatchRepository;
use App\Service\MatchLineupProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MatchController extends AbstractController
{
    public function __construct(
        private readonly MatchRepository $matchRepository,
        private readonly MatchLineupProvider $lineupProvider,
    ) {
    }

    #[Route('/match/{uniqueId}', name: 'app_match', requirements: ['uniqueId' => '.+'])]
    public function show(string $uniqueId): Response
    {
        $match = $this->matchRepository->find(trim($uniqueId));

        if (null === $match) {
            throw $this->createNotFoundException(sprintf('Match "%s" was not found.', $uniqueId));
        }

        $lineup = $this->lineupProvider->forMatch($match);

        return $this->render('match.html.twig', [
            'title' => sprintf('%s - %s', $match->getHomeTeam(), $match->getAwayTeam()),
            'match' => $match,
            'homePlayers' => $lineup['home'],
            'awayPlayers' => $lineup['away'],
            'results' => $this->lineupProvider->results($match),
            'lineupLabel' => $match->isPast() ? 'Players' : 'Expected line-up',
        ]);
    }
}
