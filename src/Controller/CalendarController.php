<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CompetitionMatch;
use App\Repository\MatchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalendarController extends AbstractController
{
    public function __construct(
        private readonly MatchRepository $matchRepository,
        #[Autowire('%env(TABT_CLUB)%')]
        private readonly string $clubId = '',
    ) {
    }

    #[Route('/calendar', name: 'app_calendar')]
    public function index(): Response
    {
        $matches = $this->matchRepository->findBy([], ['date' => 'ASC']);

        return $this->render('calendar.html.twig', [
            'title' => 'Calendar',
            'matches' => $matches,
            'events' => array_map($this->normalize(...), $matches),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(CompetitionMatch $match): array
    {
        return [
            'id' => $match->getMatchId(),
            'date' => $match->getDate(),
            'time' => $match->getTime(),
            'homeClub' => $match->getHomeClub(),
            'homeTeam' => $match->getHomeTeam(),
            'awayClub' => $match->getAwayClub(),
            'awayTeam' => $match->getAwayTeam(),
            'score' => $match->getScore(),
            'divisionName' => $match->getDivisionName(),
            'isHome' => null !== $this->clubId && $match->getHomeClub() === $this->clubId,
        ];
    }
}
