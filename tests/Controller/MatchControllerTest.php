<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\MatchController;
use App\Repository\MatchRepository;
use App\Tests\Fixtures\FakeTabtClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Yoerioptr\TabtApiClient\Client\ClientInterface;

final class MatchControllerTest extends KernelTestCase
{
    private const string PAST_MATCH_ID = 'PL/KH02/054';

    private const string UPCOMING_MATCH_ID = 'PL/KH02/055';

    public function testItRendersAPastMatchWithVenueAndPlayers(): void
    {
        $container = $this->bootWithMatches();

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/match/'.self::PAST_MATCH_ID));

        $repository = $container->get(MatchRepository::class);
        $match = $repository->find(self::PAST_MATCH_ID);

        self::assertNotNull($match);
        self::assertSame(self::PAST_MATCH_ID, $match->getMatchId());
        self::assertNotNull($match->getVenueEntry());
        self::assertSame('Sporthal Centrum', $match->getVenueEntry()->getName());
        self::assertSame('Kerkstraat 12', $match->getVenueEntry()->getStreet());

        $controller = $container->get(MatchController::class);
        $response = $controller->show(self::PAST_MATCH_ID);

        self::assertSame(200, $response->getStatusCode());

        $html = (string) $response->getContent();
        self::assertStringContainsString('Tabby A', $html);
        self::assertStringContainsString('Rivals A', $html);
        self::assertStringContainsString('Kerkstraat 12', $html);
        self::assertStringContainsString('Leuven', $html);
        self::assertStringContainsString('https://www.google.com/maps/search/?api=1&query=', $html);
        self::assertStringNotContainsString('Sporthal Centrum', $html);
        self::assertStringNotContainsString('016 123456', $html);
        self::assertStringContainsString('PAUL BAENS', $html);
        self::assertStringContainsString('TOM DE RON', $html);
        self::assertStringContainsString('RAYMOND DEKENS', $html);
        self::assertStringContainsString('DIRK BIJNENS', $html);
        self::assertStringContainsString('Set number', $html);
        self::assertStringContainsString('Home player', $html);
        self::assertStringContainsString('Away player', $html);
        self::assertStringContainsString('3-1', $html);
        self::assertStringContainsString('>D2<', $html);
        self::assertStringContainsString('20:00', $html);
        self::assertStringNotContainsString('20:00:00', $html);
        self::assertStringContainsString('>1</dd>', $html);
        self::assertStringNotContainsString('>01</dd>', $html);
        self::assertStringNotContainsString('John Doe', $html);
    }

    public function testItRendersTheExpectedLineupForAnUpcomingMatch(): void
    {
        $container = $this->bootWithMatches();

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/match/'.self::UPCOMING_MATCH_ID));

        $controller = $container->get(MatchController::class);
        $response = $controller->show(self::UPCOMING_MATCH_ID);

        self::assertSame(200, $response->getStatusCode());

        $html = (string) $response->getContent();
        self::assertStringContainsString('John Doe', $html);
        self::assertStringContainsString('Sylvester Stallone', $html);
        self::assertStringContainsString('>D2<', $html);
        self::assertStringContainsString('Expected line-up', $html);
        self::assertStringNotContainsString('PAUL BAENS', $html);
    }

    public function testItTrimsSurroundingWhitespaceFromTheMatchId(): void
    {
        $container = $this->bootWithMatches();

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/match/'.self::PAST_MATCH_ID));

        $controller = $container->get(MatchController::class);
        $response = $controller->show(self::PAST_MATCH_ID.' ');

        self::assertSame(200, $response->getStatusCode());
    }

    public function testItReturnsNotFoundForAnUnknownMatch(): void
    {
        $container = $this->bootWithMatches();

        $controller = $container->get(MatchController::class);

        $this->expectException(NotFoundHttpException::class);

        $controller->show('PL/UNKNOWN/999');
    }

    private function bootWithMatches(): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        self::bootKernel();

        $container = static::getContainer();
        $container->set(ClientInterface::class, new FakeTabtClient([
            'MatchCount' => 2,
            'TeamMatchesEntries' => [
                [
                    'MatchId' => self::PAST_MATCH_ID,
                    'MatchUniqueId' => 12345,
                    'WeekName' => '01',
                    'Date' => '2020-01-10',
                    'Time' => '20:00:00',
                    'Venue' => 1,
                    'VenueEntry' => [
                        'Name' => 'Sporthal Centrum',
                        'Street' => 'Kerkstraat 12',
                        'Town' => 'Leuven',
                        'Phone' => '016 123456',
                    ],
                    'HomeClub' => 'LK058',
                    'HomeTeam' => 'Tabby A',
                    'AwayClub' => 'OP123',
                    'AwayTeam' => 'Rivals A',
                    'Score' => '4-2',
                    'DivisionId' => 12,
                    'DivisionName' => 'Nationale 3A',
                    'IsValidated' => true,
                    'MatchDetails' => [
                        'DetailsCreated' => true,
                        'HomeCaptain' => 508072,
                        'AwayCaptain' => 510684,
                        'Referee' => 508064,
                        'HomePlayers' => [
                            'PlayerCount' => 2,
                            'DoubleTeamCount' => 0,
                            'Players' => [
                                ['Position' => 1, 'UniqueIndex' => 508072, 'FirstName' => 'PAUL', 'LastName' => 'BAENS', 'Ranking' => 'D2', 'VictoryCount' => 3],
                                ['Position' => 2, 'UniqueIndex' => 516661, 'FirstName' => 'TOM', 'LastName' => 'DE RON', 'Ranking' => 'D4', 'VictoryCount' => 2],
                            ],
                        ],
                        'AwayPlayers' => [
                            'PlayerCount' => 2,
                            'DoubleTeamCount' => 0,
                            'Players' => [
                                ['Position' => 1, 'UniqueIndex' => 510684, 'FirstName' => 'DIRK', 'LastName' => 'BIJNENS', 'Ranking' => 'D0', 'VictoryCount' => 2],
                                ['Position' => 2, 'UniqueIndex' => 506962, 'FirstName' => 'RAYMOND', 'LastName' => 'DEKENS', 'Ranking' => 'D0', 'VictoryCount' => 3],
                            ],
                        ],
                        'IndividualMatchResults' => [
                            [
                                'Position' => 1,
                                'HomePlayerMatchIndex' => 1,
                                'HomePlayerUniqueIndex' => 508072,
                                'AwayPlayerMatchIndex' => 1,
                                'AwayPlayerUniqueIndex' => 510684,
                                'HomeSetCount' => 3,
                                'AwaySetCount' => 1,
                                'Scores' => '1|5,2|7,3|8',
                            ],
                        ],
                        'MatchSystem' => 2,
                        'HomeScore' => 4,
                        'AwayScore' => 2,
                        'CommentCount' => 0,
                    ],
                ],
                [
                    'MatchId' => self::UPCOMING_MATCH_ID,
                    'MatchUniqueId' => 67890,
                    'WeekName' => 'Week 2',
                    'Date' => '2999-01-10',
                    'Time' => '20:00',
                    'HomeClub' => 'LK058',
                    'HomeTeam' => 'Tabby A',
                    'AwayClub' => 'OP999',
                    'AwayTeam' => 'Future Rivals',
                    'Score' => '',
                    'DivisionId' => 12,
                    'DivisionName' => 'Nationale 3A',
                    'IsValidated' => false,
                ],
            ],
        ]));

        return $container;
    }
}
