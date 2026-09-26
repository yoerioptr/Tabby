<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\CalendarController;
use App\Entity\CompetitionMatch;
use App\Repository\MatchRepository;
use App\Tests\Fixtures\FakeTabtClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Yoerioptr\TabtApiClient\Client\ClientInterface;

final class CalendarControllerTest extends KernelTestCase
{
    public function testItRendersTheCalendarWithTheClubMatches(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $container->set(ClientInterface::class, new FakeTabtClient([
            'MatchCount' => 3,
            'TeamMatchesEntries' => [
                [
                    'MatchId' => 'LK058H001',
                    'WeekName' => 'Week 1',
                    'Date' => '2025-09-15',
                    'Time' => '20:00:00',
                    'Venue' => 1,
                    'HomeClub' => 'LK058',
                    'HomeTeam' => 'Tabby A',
                    'AwayClub' => 'OP123',
                    'AwayTeam' => 'Rivals A',
                    'Score' => '4-2',
                    'DivisionId' => 12,
                    'DivisionName' => 'Nationale 3A',
                    'IsValidated' => true,
                ],
                [
                    'MatchId' => 'OP123H002',
                    'WeekName' => 'Week 2',
                    'Date' => '2025-09-22',
                    'Time' => '19:30',
                    'HomeClub' => 'OP123',
                    'HomeTeam' => 'Rivals A',
                    'AwayClub' => 'LK058',
                    'AwayTeam' => 'Tabby A',
                    'Score' => '',
                    'DivisionId' => 12,
                    'DivisionName' => 'Nationale 3A',
                    'IsValidated' => false,
                ],
                [
                    'MatchId' => 'LK058H003',
                    'WeekName' => 'Week 3',
                    'Time' => '20:00:00',
                    'HomeClub' => 'LK058',
                    'HomeTeam' => 'Tabby B',
                    'AwayClub' => 'OP456',
                    'AwayTeam' => 'Unscheduled Rivals',
                    'Score' => '',
                    'DivisionId' => 12,
                    'DivisionName' => 'Nationale 3A',
                    'IsValidated' => false,
                ],
            ],
        ]));

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/calendar'));

        $repository = $container->get(MatchRepository::class);
        $matches = $repository->findAll();

        self::assertCount(3, $matches);
        self::assertContainsOnlyInstancesOf(CompetitionMatch::class, $matches);

        $byId = [];

        foreach ($matches as $match) {
            $byId[$match->getMatchId()] = $match;
        }

        self::assertSame('Tabby A', $byId['LK058H001']->getHomeTeam());
        self::assertSame('Rivals A', $byId['OP123H002']->getHomeTeam());
        self::assertSame('Nationale 3A', $byId['LK058H001']->getDivisionName());
        // The API omits `Venue` for matches that are not scheduled yet.
        self::assertNull($byId['OP123H002']->getVenue());
        // Matches without a date are not scheduled yet.
        self::assertNull($byId['LK058H003']->getDate());

        $controller = $container->get(CalendarController::class);
        $response = $controller->index();

        self::assertSame(200, $response->getStatusCode());

        $html = (string) $response->getContent();
        self::assertStringContainsString('Tabby A', $html);
        self::assertStringContainsString('Rivals A', $html);
        self::assertStringContainsString('symfony--ux-react--react', $html);
        self::assertStringContainsString('Calendar', $html);
        self::assertStringContainsString('LK058H001', $html);
        self::assertStringContainsString('20:00', $html);
        self::assertStringNotContainsString('20:00:00', $html);
        self::assertStringNotContainsString('Unscheduled Rivals', $html);
        self::assertStringNotContainsString('LK058H003', $html);
        self::assertStringNotContainsString('TBC', $html);
    }
}
