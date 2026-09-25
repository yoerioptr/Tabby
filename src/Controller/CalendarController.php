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
        $matches = array_values(array_filter(
            $this->matchRepository->findBy([], ['date' => 'ASC']),
            static fn (CompetitionMatch $match): bool => null !== $match->getDate() && '' !== $match->getDate(),
        ));

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
            'time' => $match->getDisplayTime(),
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
